<?php
namespace watoki\reflect;

interface Property {

    /**
     * @return string
     */
    public function name();

    /**
     * @return bool
     */
    public function isRequired();

    /**
     * @return bool
     */
    public function canGet();

    /**
     * @return bool
     */
    public function canSet();

    /**
     * @param object $object
     * @return mixed
     */
    public function get($object);

    /**
     * @param object $object
     * @param mixed $value
     * @return void
     */
    public function set($object, $value);

    /**
     * @return null|mixed
     */
    public function defaultValue();

    /**
     * @return Type
     */
    public function type();

    /**
     * @return string|null
     */
    public function comment();
} 