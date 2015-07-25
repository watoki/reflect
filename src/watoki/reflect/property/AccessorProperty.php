<?php
namespace watoki\reflect\property;

use watoki\reflect\MethodAnalyzer;
use watoki\reflect\Property;
use watoki\reflect\TypeFactory;

class AccessorProperty extends Property {

    /** @var \ReflectionMethod|null */
    private $getter;

    /** @var \ReflectionMethod|null */
    private $setter;

    /**
     * @param TypeFactory $factory
     * @param \ReflectionMethod $method
     */
    public function __construct(TypeFactory $factory, \ReflectionMethod $method) {
        $start = substr($method->getName(), 1, 2) == 'et' ? 3 : 2;
        parent::__construct($factory, $method->getDeclaringClass(), lcfirst(substr($method->getName(), $start)));

        if (substr($method->getName(), 0, 3) == 'set') {
            $this->setter = $method;
        } else {
            $this->getter = $method;
        }
    }

    public static function isAccessor(\ReflectionMethod $method) {
        $parameters = $method->getParameters();

        return substr($method->getName(), 0, 3) == 'set' && $method->getNumberOfParameters() == 1
        || substr($method->getName(), 0, 3) == 'get' && empty($parameters)
        || substr($method->getName(), 0, 2) == 'is' && empty($parameters);
    }

    public function get($object) {
        return $this->getter->invoke($object);
    }

    public function set($object, $value) {
        $this->setter->invoke($object, $value);
    }

    public function canGet() {
        return !!$this->getter;
    }

    public function canSet() {
        return !!$this->setter;
    }

    public function typeHints() {
        if ($this->getter) {
            return $this->parseTypeHints('/@return\s+(\S+)/', $this->getter->getDocComment());
        } else if ($this->setter) {
            $parameters = $this->setter->getParameters();
            $param = $parameters[0];
            if ($param->getClass()) {
                return array($param->getClass()->getName());
            }
            return $this->parseTypeHints('/@param\s+(\S+)/', $this->setter->getDocComment());
        }
        return array();
    }

    /**
     * @return string|null
     */
    public function getComment() {
        if ($this->getter) {
            $matches = array();
            $found = preg_match('/@return\s+\S+([^*]+)/', $this->getter->getDocComment(), $matches);
            if ($found) {
                return trim($matches[1]);
            }
        } else if ($this->setter) {
            $analyzer = new MethodAnalyzer($this->setter);
            $comments = array_values($analyzer->getComments());
            return $comments[0];
        }
        return null;
    }
}