<?php
namespace watoki\reflect\type;

class IdentifierObjectType extends IdentifierType {

    public static $CLASS = __CLASS__;

    /** @var string */
    private $class;

    /**
     * @param string $target
     * @param string $identifierClass
     * @throws \Exception
     */
    function __construct($target, $identifierClass) {
        parent::__construct($target, new ClassType($identifierClass));
        $this->class = trim($identifierClass, '\\');

        if (!method_exists($identifierClass, '__toString')) {
            throw new \Exception("Method [$identifierClass::__toString] does not exist. " .
                "Identifier classes need to implement [__toString].");
        }
    }

    public function inflate($value) {
        $class = $this->class;
        return new $class($value);
    }

    public function is($value) {
        return is_object($value) && is_a($value, $this->class);
    }
}