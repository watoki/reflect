<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class UnknownType implements Type {

    public static $CLASS = __CLASS__;

    private $hint;

    /**
     * @param string|null $hint
     */
    public function __construct($hint = null) {
        $this->hint = $hint;
    }

    /**
     * @return string|null
     */
    public function getHint() {
        return $this->hint;
    }

    public function is($value) {
        return true;
    }

    public function __toString() {
        return (string)$this->hint ?: 'mixed';
    }
}