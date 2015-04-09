<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class ClassType implements Type {

    public static $CLASS = __CLASS__;

    /** @var string */
    private $class;

    /**
     * @param string $class
     */
    function __construct($class) {
        $this->class = trim($class, '\\');
    }

    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    public function is($value) {
        return is_object($value) && is_a($value, $this->class);
    }
}