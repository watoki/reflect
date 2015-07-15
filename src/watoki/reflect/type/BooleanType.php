<?php
namespace watoki\reflect\type;

class BooleanType extends PrimitiveType {

    public static $CLASS = __CLASS__;

    public function is($value) {
        return is_bool($value);
    }

    public function __toString() {
        return 'boolean';
    }
}