<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class ArrayType implements Type {

    public static $CLASS = __CLASS__;

    /** @var Type */
    private $itemType;

    function __construct(Type $itemType) {
        $this->itemType = $itemType;
    }

    /**
     * @return Type
     */
    public function getItemType() {
        return $this->itemType;
    }

    public function is($value) {
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!$this->itemType->is($item)) {
                return false;
            }
        }
        return true;
    }

    public function __toString() {
        return $this->itemType . '[]';
    }
}