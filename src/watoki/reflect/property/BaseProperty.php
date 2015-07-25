<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;
use watoki\reflect\TypeFactory;

abstract class BaseProperty implements Property {

    /** @var TypeFactory */
    protected $types;

    /** @var string */
    private $name;

    /** @var \ReflectionClass */
    protected $class;

    /**
     * @param TypeFactory $factory
     * @param \ReflectionClass $class
     * @param string $name
     */
    public function __construct(TypeFactory $factory, \ReflectionClass $class, $name) {
        $this->name = $name;
        $this->class = $class;
        $this->types = $factory;
    }

    /**
     * @return string
     */
    public function name() {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return false;
    }

    /**
     * @return null|mixed
     */
    public function defaultValue() {
        return null;
    }
} 