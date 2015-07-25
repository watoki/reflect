<?php
namespace watoki\reflect\property;

use watoki\reflect\MethodAnalyzer;
use watoki\reflect\Property;
use watoki\reflect\Type;
use watoki\reflect\type\UnknownType;
use watoki\reflect\TypeFactory;

class AccessorProperty extends BaseProperty {

    /** @var \ReflectionMethod|null */
    private $getter;

    /** @var \ReflectionMethod|null */
    private $setter;

    /**
     * @param TypeFactory $factory
     * @param \ReflectionMethod $method
     */
    public function __construct(TypeFactory $factory, \ReflectionMethod $method) {
        $start = substr($method->getName(), 0, 2) == 'is' ? 2 : 3;
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

    /**
     * @return Type
     */
    public function type() {
        if ($this->getter) {
            $analyzer = new MethodAnalyzer($this->getter);
            return $analyzer->getReturnType($this->types);
        } else if ($this->setter) {
            $analyzer = new MethodAnalyzer($this->setter);
            $types = array_values($analyzer->getTypes($this->types));
            return $types[0];
        }
        return new UnknownType();
    }

    /**
     * @return string|null
     */
    public function comment() {
        if ($this->getter) {
            $analyzer = new MethodAnalyzer($this->getter);
            return $analyzer->getReturnComment();
        } else if ($this->setter) {
            $analyzer = new MethodAnalyzer($this->setter);
            $comments = array_values($analyzer->getComments());
            return $comments[0];
        }
        return null;
    }
}