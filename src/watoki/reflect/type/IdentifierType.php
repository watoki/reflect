<?php
namespace watoki\reflect\type;

class IdentifierType {

    public static $CLASS = __CLASS__;

    /** @var string */
    private $target;

    function __construct($target) {
        $this->target = trim($target, '\\');
    }

    /**
     * @return string
     */
    public function getTarget() {
        return $this->target;
    }

} 