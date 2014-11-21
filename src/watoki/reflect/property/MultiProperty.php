<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;

class MultiProperty extends Property {

    /** @var array|Property[] */
    private $properties = array();

    public function __construct(Property $base) {
        parent::__construct($base->name(), $base->class);
    }


    public function isRequired() {
        foreach ($this->properties as $property) {
            if ($property->isRequired()) {
                return true;
            }
        }
        return false;
    }

    public function canGet() {
        foreach ($this->properties as $property) {
            if ($property->canGet()) {
                return true;
            }
        }
        return false;
    }

    public function canSet() {
        foreach ($this->properties as $property) {
            if ($property->canSet()) {
                return true;
            }
        }
        return false;
    }

    public function get($object) {
        foreach ($this->properties as $property) {
            if ($property->canGet() && $property->get($object) !== null) {
                return $property->get($object);
            }
        }
        return null;
    }

    public function set($object, $value) {
        foreach ($this->properties as $property) {
            if ($property->canSet()) {
                $property->set($object, $value);
            }
        }
        return null;
    }

    public function defaultValue() {
        foreach ($this->properties as $property) {
            if ($property->defaultValue()) {
                return $property->defaultValue();
            }
        }
        return null;
    }

    public function add(Property $property) {
        $this->properties[] = $property;
    }

    public function typeHints() {
        $types = array();
        foreach ($this->properties as $property) {
            $types = array_merge($types, $property->typeHints());
        }
        return array_unique($types);
    }
}