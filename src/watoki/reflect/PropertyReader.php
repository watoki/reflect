<?php
namespace watoki\reflect;

use watoki\collections\Map;

class PropertyReader {

    /** @var \ReflectionClass */
    private $class;

    function __construct($class) {
        $this->class = new \ReflectionClass($class);
    }

    /**
     * Derives properties from constructor, public instance variables, getters and setters.
     *
     * @param object|null $object If provided, dynamic (run-time) variables are read as well
     * @return \watoki\collections\Map|Property[] indexed by property name
     */
    public function readInterface($object = null) {
        $properties = new Map();

        if ($this->class->getConstructor()) {
            foreach ($this->class->getConstructor()->getParameters() as $parameter) {
                $this->accumulate($properties,
                    new property\ConstructorProperty($this->class->getConstructor(), $parameter));
            }
        }

        foreach ($this->class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $this->accumulate($properties,
                    new property\InstanceVariableProperty($property));
            }
        }

        if (is_object($object)) {
            foreach ($object as $name => $value) {
                $this->accumulate($properties,
                    new property\DynamicProperty($name, new \ReflectionClass($object)));
            }
        }

        foreach ($this->class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (property\AccessorProperty::isAccessor($method) && !$method->isStatic()) {
                $this->accumulate($properties,
                    new property\AccessorProperty($method));
            }
        }

        return $properties;
    }

    /**
     * @param null|int $filter Filters of ReflectionProperty::FILTER_*
     * @return Map|Property[] indexed by name
     */
    public function readState($filter = null) {
        $properties = new Map();
        if (!$filter) {
            $filter = \ReflectionProperty::IS_PRIVATE
                | \ReflectionProperty::IS_PROTECTED
                | \ReflectionProperty::IS_PUBLIC;
        }

        foreach ($this->class->getProperties($filter) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $this->accumulate($properties,
                new property\InstanceVariableProperty($property));
        }

        return $properties;
    }

    private function accumulate(Map $acc, Property $property) {
        if (!$acc->has($property->name())) {
            $acc->set($property->name(), $property);
        } else {
            $multi = $acc->get($property->name());
            if (!($multi instanceof property\MultiProperty)) {
                $multi = new property\MultiProperty($property);
                $multi->add($acc->get($property->name()));
                $acc->set($property->name(), $multi);
            }
            $multi->add($property);
        }
    }

} 