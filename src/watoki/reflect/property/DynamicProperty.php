<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;

class DynamicProperty extends Property {

    public function type() {
        return null;
    }

    public function canGet() {
        return true;
    }

    public function canSet() {
        return true;
    }

    public function get($object) {
        $name = $this->name();
        return $object->$name;
    }

    public function set($object, $value) {
        $name = $this->name();
        $object->$name = $value;
    }
}