<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class IntegerType extends LongType {

    public static $CLASS = __CLASS__;

    public function is($value) {
        return is_int($value);
    }
}