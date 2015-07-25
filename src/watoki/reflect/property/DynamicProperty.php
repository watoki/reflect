<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;

class DynamicProperty extends Property {

    public function typeHints() {
        return array();
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

    /**
     * @return string|null
     */
    public function getComment() {
        return null;
    }
}