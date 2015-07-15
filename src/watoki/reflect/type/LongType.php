<?php
namespace watoki\reflect\type;

class LongType extends PrimitiveType {

    public static $CLASS = __CLASS__;

    public function is($value) {
        return is_long($value);
    }

    public function __toString() {
        return 'long';
    }
}