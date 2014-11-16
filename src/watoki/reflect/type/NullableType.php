<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class NullableType extends PrimitiveType {

    public static $CLASS = __CLASS__;

    /** @var Type */
    private $type;

    function __construct(Type $type) {
        $this->type = $type;
    }

    /**
     * @return Type
     */
    public function getType() {
        return $this->type;
    }

} 