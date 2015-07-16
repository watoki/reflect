<?php
namespace watoki\reflect;

interface Type {

    /**
     * @param mixed $value
     * @return boolean
     */
    public function is($value);

    /**
     * @return string
     */
    public function __toString();
}