<?php
namespace watoki\reflect;

use watoki\collections\Map;
use watoki\reflect\property\BaseProperty;

class PropertyReader {

    /** @var \ReflectionClass */
    private $class;

    /** @var TypeFactory */
    private $factory;

    /**
     * @param TypeFactory $factory
     * @param string $class
     */
    function __construct(TypeFactory $factory, $class) {
        $this->class = new \ReflectionClass($class);
        $this->factory = $factory;
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
                    new property\ConstructorProperty($this->factory, $this->class->getConstructor(), $parameter));
            }
        }

        $declaredProperties = array();
        foreach ($this->class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $declaredProperties[] = $property->name;
                $this->accumulate($properties,
                    new property\InstanceVariableProperty($this->factory, $property));
            }
        }

        if (is_object($object)) {
            foreach ($object as $name => $value) {
                if (!in_array($name, $declaredProperties)) {
                    $this->accumulate($properties,
                        new property\DynamicProperty($this->factory, new \ReflectionClass($object), $name));
                }
            }
        }

        foreach ($this->class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (property\AccessorProperty::isAccessor($method) && !$method->isStatic()) {
                $this->accumulate($properties,
                    new property\AccessorProperty($this->factory, $method));
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

        $class = $this->class;
        while ($class) {
            foreach ($class->getProperties($filter) as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $this->accumulate($properties,
                    new property\InstanceVariableProperty($this->factory, $property));
            }

            $class = $class->getParentClass();
        }

        return $properties;
    }

    private function accumulate(Map $acc, BaseProperty $property) {
        if (!$acc->has($property->name())) {
            $acc->set($property->name(), $property);
        } else {
            $multi = $acc->get($property->name());
            if (!($multi instanceof property\MultiProperty)) {
                $multi = new property\MultiProperty($this->factory, $property);
                $multi->add($acc->get($property->name()));
                $acc->set($property->name(), $multi);
            }
            $multi->add($property);
        }
    }

} 