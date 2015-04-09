<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class DoubleType extends PrimitiveType {

    public static $CLASS = __CLASS__;

    public function is($value) {
        return is_double($value);
    }
}