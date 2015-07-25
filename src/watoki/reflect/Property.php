<?php
namespace watoki\reflect;

abstract class Property {

    /** @var TypeFactory */
    protected $factory;

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
        $this->factory = $factory;
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
        return $this->factory->fromTypeHints($this->typeHints(), $this->class);
    }

    /**
     * @return array|string[] unresolved type hints
     */
    abstract protected function typeHints();

    /**
     * @return string|null
     */
    abstract public function getComment();

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