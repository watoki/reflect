<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class NullableType implements Type {

    public static $CLASS = __CLASS__;

    /** @var string */
    private $type;

    function __construct($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

} 