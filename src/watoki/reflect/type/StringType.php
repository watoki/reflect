<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class StringType  extends PrimitiveType {

    public static $CLASS = __CLASS__;

    public function is($value) {
        return is_string($value);
    }

    public function __toString() {
        return 'string';
    }
}