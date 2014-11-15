<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;

class InstanceVariableProperty extends Property {

    /** @var \ReflectionProperty */
    private $property;

    public function __construct(\ReflectionProperty $property, $required = false, $type = null) {
        parent::__construct($property->getName(), $required, $type);
        $property->setAccessible(true);
        $this->property = $property;
    }

    public function get($object) {
        return $this->property->getValue($object);
    }

    public function set($object, $value) {
        $this->property->setValue($object, $value);
    }

    public function canGet() {
        return true;
    }

    public function canSet() {
        return true;
    }

    public function defaultValue() {
        if ($this->property->isDefault()) {
            $defaultProperties = $this->property->getDeclaringClass()->getDefaultProperties();
            return $defaultProperties[$this->property->getName()];
        }
        return null;
    }

    public function type() {
        return $this->findType('/@var\s+(\S+).*/', $this->property->getDocComment(),
            $this->property->getDeclaringClass());
    }
}