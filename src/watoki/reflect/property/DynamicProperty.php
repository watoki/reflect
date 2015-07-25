<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;
use watoki\reflect\Type;
use watoki\reflect\type\UnknownType;

class DynamicProperty extends BaseProperty {

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

    public function comment() {
        return null;
    }

    public function type() {
        return new UnknownType();
    }
}