<?php
namespace watoki\reflect;

use watoki\collections\Map;

class PropertyReader {

    private $class;

    function __construct($class) {
        $this->class = $class;
    }

    /**
     * @param object|null $object If provided, dynamic (run-time) properties are read as well
     * @return \watoki\collections\Map|Property[] indexed by property name
     */
    public function readProperties($object = null) {
        $properties = new Map();
        $reflection = new \ReflectionClass($object ? : $this->class);

        $add = function (Property $property) use ($properties) {
            if (!$properties->has($property->name())) {
                $properties[$property->name()] = $property;
            } else {
                $multi = $properties[$property->name()];
                if (!($multi instanceof property\MultiProperty)) {
                    $multi = new property\MultiProperty($property->name());
                    $multi->add($properties[$property->name()]);
                    $properties[$property->name()] = $multi;
                }
                $multi->add($property);
            }
        };

        if ($reflection->getConstructor()) {
            foreach ($reflection->getConstructor()->getParameters() as $parameter) {
                $add(new property\ConstructorProperty($reflection->getConstructor(), $parameter));
            }
        }

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $add(new property\PublicProperty($property));
        }

        if (is_object($object)) {
            foreach ($object as $name => $value) {
                $add(new property\DynamicProperty($name));
            }
        }

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (property\AccessorProperty::isAccessor($method)) {
                $add(new property\AccessorProperty($method));
            }
        }

        return $properties;
    }

} 