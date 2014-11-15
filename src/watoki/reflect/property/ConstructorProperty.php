<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;

class ConstructorProperty extends Property {

    /** @var \ReflectionMethod */
    private $constructor;

    /** @var \ReflectionParameter */
    private $parameter;

    public function __construct(\ReflectionMethod $constructor, \ReflectionParameter $parameter, $required = false, $type = null) {
        parent::__construct($parameter->getName(), $required, $type);
        $this->constructor = $constructor;
        $this->parameter = $parameter;
    }

    public function isRequired() {
        return !$this->parameter->isDefaultValueAvailable();
    }

    public function canGet() {
        return false;
    }

    public function canSet() {
        return true;
    }

    public function get($object) {
    }

    public function set($object, $value) {
    }

    public function defaultValue() {
        return $this->parameter->isDefaultValueAvailable() ? $this->parameter->getDefaultValue() : null;
    }

    public function type() {
        $class = $this->constructor->getDeclaringClass();

        if ($this->parameter->getClass()) {
            return $this->resolveClassType($this->parameter->getClass()->getName(), $class);
        }

        $pattern = '/@param\s+(\S+)\s+\$' . $this->parameter->getName() . '/';
        return $this->findType($pattern, $this->constructor->getDocComment(), $class);
    }
}