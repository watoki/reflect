<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class NullType implements Type {

    public function is($value) {
        return is_null($value);
    }
}