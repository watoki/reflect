<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;
use watoki\reflect\TypeFactory;

class ConstructorProperty extends Property {

    /** @var \ReflectionMethod */
    private $constructor;

    /** @var \ReflectionParameter */
    private $parameter;

    /**
     * @param TypeFactory $factory
     * @param \ReflectionMethod $constructor
     * @param \ReflectionParameter $parameter
     */
    public function __construct(TypeFactory $factory, \ReflectionMethod $constructor, \ReflectionParameter $parameter) {
        parent::__construct($factory, $constructor->getDeclaringClass(), $parameter->getName());
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

    public function typeHints() {
        if ($this->parameter->getClass()) {
            return array($this->parameter->getClass()->getName());
        }

        $pattern = '/@param\s+(\S+)\s+\$' . $this->parameter->getName() . '/';
        return $this->parseTypeHints($pattern, $this->constructor->getDocComment());
    }
}