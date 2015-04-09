<?php
namespace watoki\reflect;

use watoki\reflect\type\UnknownType;

class TypeFactory {

    private static $TARGET_REFERENCE = 'TARGET';

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
        } else {
            foreach ($hints as $hint) {
                if (strtolower(substr($hint, -3)) == '-id') {
                    $resolved = $this->resolver->resolve(substr($hint, 0, -3));
                    if ($resolved) {
                        $hints = array_values(array_diff($hints, array($hint)));
                        return new type\IdentifierType($resolved, $this->fromTypeHints($hints));
                    }
                }
            }
        }

        $types = array();
        foreach ($hints as $hint) {
            $types[] = $this->fromTypeHint($hint);
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

        if (strtolower(substr($hint, -2)) == 'id') {
            $hint = new \ReflectionClass($resolved);
            if ($hint->hasMethod(self::$TARGET_REFERENCE)) {
                return new type\IdentifierObjectType($hint->getMethod(self::$TARGET_REFERENCE)->invoke(null), $resolved);
            } else if ($hint->hasConstant(self::$TARGET_REFERENCE)) {
                return new type\IdentifierObjectType($hint->getConstant(self::$TARGET_REFERENCE), $resolved);
            } else if (class_exists(substr($hint->getName(), 0, -2))) {
                return new type\IdentifierObjectType(substr($hint->getName(), 0, -2), $resolved);
            }
        }

        return new type\ClassType($resolved);
    }
}