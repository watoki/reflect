<?php
namespace watoki\reflect;

use watoki\reflect\type\UnknownType;

class TypeFactory {

    /**
     * @param array|string[] $hints
     * @param \ReflectionClass $class
     * @return Type
     */
    public function fromTypeHints(array $hints, \ReflectionClass $class) {
        if (!$hints) {
            return new type\UnknownType('');
        }

        if (count($hints) == 1) {
            return $this->fromTypeHint($hints[0], $class);
        }

        if (in_array('null', $hints)) {
            $hints = array_values(array_diff($hints, array('null')));
            return new type\NullableType($this->fromTypeHints($hints, $class));

        } else if (in_array('array', $hints)) {
            $hints = array_values(array_diff($hints, array('array')));
            $hints = array_map(function ($type) {
                return str_replace('[]', '', $type);
            }, $hints);
            return new type\ArrayType($this->fromTypeHints($hints, $class));
        }

        $types = array();
        foreach ($hints as $hint) {
            $type = $this->fromTypeHint($hint, $class);
            if (!in_array($type, $types)) {
                $types[] = $type;
            }
        }
        if (count($types) == 1) {
            return $types[0];
        }
        return new type\MultiType($types);
    }

    /**
     * @param string $hint
     * @param \ReflectionClass $class
     * @return Type
     */
    public function fromTypeHint($hint, \ReflectionClass $class) {
        switch (strtolower($hint)) {
            case 'null':
            case 'void':
                return new type\NullType();
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
            case 'array':
                return new type\ArrayType(new UnknownType(''));
        }

        return $this->resolveClassHint($hint, $class);
    }

    /**
     * @param string $hint
     * @param \ReflectionClass $class
     * @return Type
     */
    protected function resolveClassHint($hint, \ReflectionClass $class) {
        $resolver = new ClassResolver($class);
        $resolved = $resolver->resolve($hint);

        if (!$resolved) {
            return new type\UnknownType($hint);
        }

        return new type\ClassType($resolved);
    }
}