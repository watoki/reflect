<?php
namespace watoki\reflect\type;

class MultiType {

    public static $CLASS = __CLASS__;

    /** @var object[] */
    private $types;

    function __construct($type) {
        $this->types = $type;
    }

    /**
     * @return \object[]
     */
    public function getTypes() {
        return $this->types;
    }

}