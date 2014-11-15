<?php
namespace watoki\reflect;

class MethodAnalyzer {

    private $method;

    function __construct(\ReflectionMethod $method) {
        $this->method = $method;
    }

    /**
     * @param array $args
     * @param callable $injector
     * @return array
     */
    public function fillParameters(array $args, $injector) {
        $argArray = array();
        foreach ($this->method->getParameters() as $param) {
            try {
                $argArray[$param->getName()] = $this->fillParameter($param, $args, $injector);
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
     * @param $injector
     * @throws \Exception
     * @return object
     */
    private function fillParameter(\ReflectionParameter $param, array $args, $injector) {
        if ($this->hasValue($param, $args)) {
            return $this->getValue($param, $args);
        } else if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        } else if ($this->isMarkedInjectable($param)) {
            $type = $this->getTypeHint($param);
            if (!$type) {
                throw new \InvalidArgumentException("Argument not given and no type hint found.");
            }
            return call_user_func($injector, $type);
        } else {
            throw new \InvalidArgumentException("Argument not given and not marked as injectable.");
        }
    }

    private function isMarkedInjectable(\ReflectionParameter $param) {
        $pattern = '/@param.+\$' . $param->getName() . '.+' . \watoki\factory\Injector::INJECTION_MARKER . '/';
        return preg_match($pattern, $this->method->getDocComment());
    }

    public function getTypeHint(\ReflectionParameter $param) {
        if ($param->getClass()) {
            return $param->getClass()->getName();
        }

        $matches = array();
        $pattern = '/@param\s+(\S+)\s+\$' . $param->getName() . '/';
        $found = preg_match($pattern, $this->method->getDocComment(), $matches);

        if (!$found) {
            return null;
        }
        $type = $matches[1];

        $resolver = new ClassResolver($this->method->getDeclaringClass());
        $resolved = $resolver->resolve($type);

        return $resolved ? : $type;
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

}