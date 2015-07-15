<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class FloatType extends DoubleType {

    public static $CLASS = __CLASS__;

    public function is($value) {
        return is_double($value);
    }

    public function __toString() {
        return 'float';
    }
}