<?php
namespace watoki\reflect\type;

use watoki\reflect\Type;

class UnknownType implements Type {

    public static $CLASS = __CLASS__;

    private $hint;

    /**
     * @param string $hint
     */
    public function __construct($hint) {
        $this->hint = $hint;
    }

    /**
     * @return string
     */
    public function getHint() {
        return $this->hint;
    }

} 