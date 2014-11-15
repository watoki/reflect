<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class IdentifierType implements Type {

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