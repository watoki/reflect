<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;
use watoki\reflect\Type;
use watoki\reflect\TypeFactory;

class InstanceVariableProperty extends BaseProperty {

    /** @var \ReflectionProperty */
    private $property;

    /**
     * @param TypeFactory $factory
     * @param \ReflectionProperty $property
     */
    public function __construct(TypeFactory $factory, \ReflectionProperty $property) {
        parent::__construct($factory, $property->getDeclaringClass(), $property->getName());
        $property->setAccessible(true);
        $this->property = $property;
    }

    public function get($object) {
        return $this->property->getValue($object);
    }

    public function set($object, $value) {
        $this->property->setValue($object, $value);
    }

    public function canGet() {
        return true;
    }

    public function canSet() {
        return true;
    }

    public function defaultValue() {
        if ($this->property->isDefault()) {
            $defaultProperties = $this->property->getDeclaringClass()->getDefaultProperties();
            return $defaultProperties[$this->property->getName()];
        }
        return null;
    }

    /**
     * @return Type
     */
    public function type() {
        return $this->types->fromString($this->match('/@var\s+(\S+).*/'), $this->class);
    }

    /**
     * @return string|null
     */
    public function comment() {
        $pattern = '/@var\s+\S+([^*]*)/';
        return trim($this->match($pattern));
    }

    /**
     * @param $pattern
     * @return null|string
     */
    private function match($pattern) {
        $matches = array();
        $found = preg_match($pattern, $this->property->getDocComment(), $matches);
        if (!$found) {
            return null;
        }
        return $matches[1];
    }
}