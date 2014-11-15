<?php
namespace watoki\reflect;

abstract class Property {

    private $name;

    const TARGET_REFERENCE = 'TARGET';

    public function __construct($name) {
        $this->name = $name;
    }

    public function name() {
        return $this->name;
    }

    public function isRequired() {
        return false;
    }

    abstract public function type();

    abstract public function canGet();

    abstract public function canSet();

    abstract public function get($object);

    abstract public function set($object, $value);

    public function defaultValue() {
        return null;
    }

    protected function findType($pattern, $docComment, \ReflectionClass $class) {
        $matches = array();
        $found = preg_match($pattern, $docComment, $matches);
        if (!$found) {
            return null;
        }
        $type = $matches[1];

        if (strpos($type, '|') !== false) {
            $types = explode('|', $type);
        } else {
            $types = array($type);
        }

        return $this->getType($types, $class);
    }

    public function getType($types, \ReflectionClass $class) {
        if (count($types) > 1) {
            if (in_array('null', $types)) {
                $types = array_values(array_diff($types, array('null')));
                return new type\NullableType($this->getType($types, $class));
            } else if (in_array('array', $types)) {
                $types = array_values(array_diff($types, array('array')));
                $types = array_map(function ($type) {
                    return str_replace('[]', '', $type);
                }, $types);
                return new type\ArrayType($this->getType($types, $class));
            }
            $that = $this;
            return new type\MultiType(array_map(function ($type) use ($that, $class) {
                return $that->getType(array($type), $class);
            }, $types));
        }

        $type = $types[0];

        switch ($type) {
            case 'int':
            case 'integer':
                return new type\IntegerType();
            case 'long':
                return new type\LongType();
            case 'float':
                return new type\FloatType();
            case 'double':
                return new type\DoubleType();
            case 'string':
                return new type\StringType();
            case 'bool':
            case 'boolean':
                return new type\BooleanType();
        }

        $resolver = new ClassResolver($class);

        if (strtolower(substr($type, -3)) == '-id') {
            $resolved = $resolver->resolve(substr($type, 0, -3));
            if ($resolved) {
                return new type\IdentifierType($resolved);
            } else {
                return null;
            }
        }

        return $this->resolveClassType($type, $class);
    }

    /**
     * @param $type
     * @param \ReflectionClass $class
     * @return null|type\ClassType|type\IdentifierObjectType
     */
    protected function resolveClassType($type, \ReflectionClass $class) {
        $resolver = new ClassResolver($class);

        if (strtolower(substr($type, -2)) == 'id') {
            $resolved = $resolver->resolve($type);
            if ($resolved) {
                $class = new \ReflectionClass($resolved);
                if ($class->hasMethod(self::TARGET_REFERENCE)) {
                    return new type\IdentifierObjectType($class->getMethod(self::TARGET_REFERENCE)->invoke(null), $resolved);
                } else if ($class->hasConstant(self::TARGET_REFERENCE)) {
                    return new type\IdentifierObjectType($class->getConstant(self::TARGET_REFERENCE), $resolved);
                } else if (class_exists(substr($class->getName(), 0, -2))) {
                    return new type\IdentifierObjectType(substr($class->getName(), 0, -2), $resolved);
                }
            }
        }

        $resolved = $resolver->resolve($type);
        if ($resolved) {
            return new type\ClassType($resolved);
        }
        return null;
    }

} 