<?php
namespace watoki\reflect;

use watoki\reflect\type\NullableType;

class MethodAnalyzer {

    private $method;

    function __construct(\ReflectionMethod $method) {
        $this->method = $method;
    }

    /**
     * @param array $args
     * @param callable $injector
     * @param callable $parameterFilter
     * @return array
     */
    public function fillParameters(array $args, $injector, $parameterFilter) {
        $argArray = array();
        foreach ($this->method->getParameters() as $param) {
            try {
                $argArray[$param->getName()] = $this->fillParameter($param, $args, $injector, $parameterFilter);
            } catch (\Exception $e) {
                $this->throwException($param, $e);
            }
        }
        return $argArray;
    }

    private function throwException(\ReflectionParameter $param, \Exception $e) {
        $methodName = $this->method->getDeclaringClass()->getName() . '::' . $this->method->getName();
        throw new \InvalidArgumentException("Cannot fill parameter [{$param->getName()}] of [$methodName]: "
            . $e->getMessage(), 0, $e);
    }

    public function normalize(array $args) {
        $normalized = array();
        foreach ($this->method->getParameters() as $param) {
            if ($this->hasValue($param, $args)) {
                $normalized[$param->getName()] = $this->getValue($param, $args);
            }
        }
        return $normalized;
    }

    public function getParameter($name) {
        foreach ($this->method->getParameters() as $param) {
            if ($param->getName() == $name) {
                return $param;
            }
        }
        throw new \Exception("Parameter [$name] dow not exist");
    }

    /**
     * @param \ReflectionParameter $param
     * @param array $args
     * @param callable $injector
     * @param callable $argumentsFilter
     * @return object
     * @throws \Exception
     */
    private function fillParameter(\ReflectionParameter $param, array $args, $injector, $argumentsFilter) {
        if ($this->hasValue($param, $args)) {
            return $this->getValue($param, $args);
        } else if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        } else if ($this->isInjectable($param, $argumentsFilter)) {
            $type = $this->getTypeHint($param);
            if (!$type) {
                throw new \InvalidArgumentException("Argument not given and no type hint found.");
            }
            return call_user_func($injector, $type);
        } else {
            throw new \InvalidArgumentException("Argument not given and not injectable.");
        }
    }

    private function isInjectable(\ReflectionParameter $param, $argumentsFilter) {
        return call_user_func($argumentsFilter, $param);
    }

    /**
     * @param TypeFactory $factory
     * @return Type[] indexed by parameter name
     */
    public function getTypes(TypeFactory $factory) {
        $types = array();
        foreach ($this->method->getParameters() as $parameter) {
            $types[$parameter->getName()] = $this->getType($parameter, $factory);
        }
        return $types;
    }

    /**
     * @param \ReflectionParameter $param
     * @param TypeFactory $types
     * @return Type
     */
    public function getType(\ReflectionParameter $param, TypeFactory $types) {
        $type = $types->fromString($this->getTypeHint($param), $param->getDeclaringClass());
        if (!($type instanceof NullableType) && $param->isDefaultValueAvailable() && is_null($param->getDefaultValue())) {
            $type = new NullableType($type);
        }
        return $type;
    }

    public function getReturnType(TypeFactory $types) {
        return $types->fromString($this->match('/@return\s+(\S+)/'), $this->method->getDeclaringClass());
    }

    /**
     * @param \ReflectionParameter $param
     * @return null|string
     */
    public function getTypeHint(\ReflectionParameter $param) {
        if ($param->getClass()) {
            return $param->getClass()->getName();
        }

        $type = $this->match('/@param\s+(\S+)\s+\$' . $param->getName() . '/');
        if (!$type) {
            return null;
        }

        $resolver = new ClassResolver($this->method->getDeclaringClass());
        $resolved = $resolver->resolve($type);

        return $resolved ? : $type;
    }

    /**
     * @return array|string[] indexed by parameter name
     */
    public function getComments() {
        $types = array();
        foreach ($this->method->getParameters() as $parameter) {
            $types[$parameter->getName()] = $this->getComment($parameter);
        }
        return $types;
    }

    /**
     * @param \ReflectionParameter $param
     * @return null|string
     */
    public function getComment(\ReflectionParameter $param) {
        return trim($this->match('/@param[^$]+\$' . $param->getName() . '(.*?)\n/'));
    }

    public function getReturnComment() {
        return trim($this->match('/@return\s+\S+([^*]+)/'));
    }

    /**
     * @param \ReflectionParameter $param
     * @param array $args
     * @throws \Exception
     * @return mixed
     */
    private function getValue(\ReflectionParameter $param, array $args) {
        if (array_key_exists($param->getName(), $args)) {
            return $args[$param->getName()];
        } else if (array_key_exists($param->getPosition(), $args)) {
            return $args[$param->getPosition()];
        }
        $keys = implode(', ', array_keys($args));
        throw new \Exception("Value of [{$param->getName()}] not found in [$keys].");
    }

    private function hasValue(\ReflectionParameter $param, array $args) {
        return array_key_exists($param->getName(), $args) || array_key_exists($param->getPosition(), $args);
    }

    private function match($pattern) {
        $matches = array();
        $found = preg_match($pattern, $this->method->getDocComment(), $matches);
        if (!$found) {
            return null;
        }
        return $matches[1];
    }

}