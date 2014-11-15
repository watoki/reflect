<?php
namespace watoki\reflect\type;

class ClassType {

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

} 