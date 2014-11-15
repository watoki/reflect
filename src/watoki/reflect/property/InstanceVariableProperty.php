<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;

class InstanceVariableProperty extends Property {

    /** @var \ReflectionProperty */
    private $property;

    /**
     * @param \ReflectionProperty $property
     */
    public function __construct(\ReflectionProperty $property) {
        parent::__construct($property->getName(), $property->getDeclaringClass());
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

    public function typeHints() {
        return $this->parseTypeHints('/@var\s+(\S+).*/', $this->property->getDocComment(),
            $this->property->getDeclaringClass());
    }
}