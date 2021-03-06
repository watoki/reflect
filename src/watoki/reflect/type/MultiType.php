<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class MultiType implements Type {

    public static $CLASS = __CLASS__;

    /** @var Type[] */
    private $types;

    /**
     * @param array|Type[] $types
     */
    function __construct(array $types) {
        $this->types = $types;
    }

    /**
     * @return Type[]
     */
    public function getTypes() {
        return $this->types;
    }

    public function is($value) {
        foreach ($this->types as $type) {
            if ($type->is($value)) {
                return true;
            }
        }
        return false;
    }

    public function __toString() {
        return implode('|', $this->types);
    }
}