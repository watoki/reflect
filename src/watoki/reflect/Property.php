<?php
namespace watoki\reflect;

abstract class Property {

    /** @var string */
    private $name;

    /** @var \ReflectionClass */
    protected $class;

    /**
     * @param string $name
     * @param \ReflectionClass $class
     */
    public function __construct($name, \ReflectionClass $class) {
        $this->name = $name;
        $this->class = $class;
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
     * @return bool
     */
    abstract public function canGet();

    /**
     * @return bool
     */
    abstract public function canSet();

    /**
     * @param object $object
     * @return mixed
     */
    abstract public function get($object);

    /**
     * @param object $object
     * @param mixed $value
     * @return void
     */
    abstract public function set($object, $value);

    /**
     * @return null|mixed
     */
    public function defaultValue() {
        return null;
    }

    /**
     * @return Type
     */
    public function type() {
        $factory = new TypeFactory($this->class);
        return $factory->fromTypeHints($this->typeHints());
    }

    /**
     * @return array|string[] unresolved type hints
     */
    abstract protected function typeHints();

    /**
     * @param $pattern
     * @param $haystack
     * @return array
     */
    protected function parseTypeHints($pattern, $haystack) {
        $matches = array();
        $found = preg_match($pattern, $haystack, $matches);
        if (!$found) {
            return array();
        }
        $type = $matches[1];

        if (strpos($type, '|') !== false) {
            return explode('|', $type);
        } else {
            return array($type);
        }
    }

} 