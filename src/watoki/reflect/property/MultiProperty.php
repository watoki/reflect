<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;
use watoki\reflect\type\MultiType;

class MultiProperty extends Property {

    /** @var array|Property[] */
    private $properties = array();

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

    public function type() {
        $types = array();
        foreach ($this->properties as $property) {
            $type = $property->type();
            if ($type) {
                $types[] = $type;
            }
        }
        if (!$types) {
            return null;
        } else if (count($types) == 1) {
            return $types[0];
        } else {
            return new MultiType($types);
        }
    }
}