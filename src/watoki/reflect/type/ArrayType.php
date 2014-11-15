<?php
namespace watoki\reflect\type;

class ArrayType {

    public static $CLASS = __CLASS__;

    /** @var object */
    private $itemType;

    function __construct($itemType) {
        $this->itemType = $itemType;
    }

    /**
     * @return object
     */
    public function getItemType() {
        return $this->itemType;
    }

} 