<?php
namespace watoki\reflect\property;

use watoki\reflect\MethodAnalyzer;
use watoki\reflect\Property;
use watoki\reflect\Type;
use watoki\reflect\TypeFactory;

class ConstructorProperty extends BaseProperty {

    /** @var \ReflectionMethod */
    private $constructor;

    /** @var \ReflectionParameter */
    private $parameter;

    /**
     * @param TypeFactory $factory
     * @param \ReflectionMethod $constructor
     * @param \ReflectionParameter $parameter
     */
    public function __construct(TypeFactory $factory, \ReflectionMethod $constructor, \ReflectionParameter $parameter) {
        parent::__construct($factory, $constructor->getDeclaringClass(), $parameter->getName());
        $this->constructor = $constructor;
        $this->parameter = $parameter;
    }

    public function isRequired() {
        return !$this->parameter->isDefaultValueAvailable();
    }

    public function canGet() {
        return false;
    }

    public function canSet() {
        return true;
    }

    public function get($object) {
    }

    public function set($object, $value) {
    }

    public function defaultValue() {
        return $this->parameter->isDefaultValueAvailable() ? $this->parameter->getDefaultValue() : null;
    }

    /**
     * @return Type
     */
    public function type() {
        $analyzer = new MethodAnalyzer($this->constructor);
        return $analyzer->getType($this->parameter, $this->types);
    }

    /**
     * @return string|null
     */
    public function comment() {
        $analyzer = new MethodAnalyzer($this->constructor);
        return $analyzer->getComment($this->parameter);
    }
}