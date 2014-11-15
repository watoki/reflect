<?php
namespace watoki\reflect\type;

class NullableType {

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