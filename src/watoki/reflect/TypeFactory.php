<?php
namespace watoki\reflect;

use watoki\reflect\type\UnknownType;

class TypeFactory {

    /** @var \watoki\reflect\ClassResolver */
    private $resolver;

    public function __construct(\ReflectionClass $class) {
        $this->resolver = new ClassResolver($class);
    }

    /**
     * @param array $hints
     * @return Type
     */
    public function fromTypeHints(array $hints) {
        if (!$hints) {
            return new type\UnknownType('');
        }

        if (count($hints) == 1) {
            return $this->fromTypeHint($hints[0]);
        }

        if (in_array('null', $hints)) {
            $hints = array_values(array_diff($hints, array('null')));
            return new type\NullableType($this->fromTypeHints($hints));

        } else if (in_array('array', $hints)) {
            $hints = array_values(array_diff($hints, array('array')));
            $hints = array_map(function ($type) {
                return str_replace('[]', '', $type);
            }, $hints);
            return new type\ArrayType($this->fromTypeHints($hints));
        }

        $types = array();
        foreach ($hints as $hint) {
            $type = $this->fromTypeHint($hint);
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
     * @param $hint
     * @return Type
     */
    public function fromTypeHint($hint) {
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

        return $this->resolveClassHint($hint);
    }

    /**
     * @param $hint
     * @return Type
     */
    protected function resolveClassHint($hint) {
        $resolved = $this->resolver->resolve($hint);

        if (!$resolved) {
            return new type\UnknownType($hint);
        }

        return new type\ClassType($resolved);
    }
}