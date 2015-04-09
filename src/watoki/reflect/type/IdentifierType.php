<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class IdentifierType implements Type {

    public static $CLASS = __CLASS__;

    /** @var string */
    private $target;

    /** @var Type */
    private $primitive;

    /**
     * @param string $target Identified class
     * @param Type $primitive
     */
    public function __construct($target, Type $primitive) {
        $this->target = trim($target, '\\');
        $this->primitive = $primitive;
    }

    /**
     * @return string
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @return \watoki\reflect\Type
     */
    public function getPrimitive() {
        return $this->primitive;
    }

    public function is($value) {
        return $this->primitive->is($value);
    }
}