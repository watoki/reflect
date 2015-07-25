<?php
namespace watoki\reflect\property;

use watoki\reflect\Property;
use watoki\reflect\Type;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\UnknownType;
use watoki\reflect\TypeFactory;

class MultiProperty extends BaseProperty {

    /** @var array|Property[] */
    private $properties = array();

    public function __construct(TypeFactory $factory, BaseProperty $base) {
        parent::__construct($factory, $base->class, $base->name());
    }

    public function isRequired() {
        foreach ($this->properties as $property) {
            if ($property->isRequired()) {
                return true;
            }
        }
        return false;
    }

    public function canGet() {
        foreach ($this->properties as $property) {
            if ($property->canGet()) {
                return true;
            }
        }
        return false;
    }

    public function canSet() {
        foreach ($this->properties as $property) {
            if ($property->canSet()) {
                return true;
            }
        }
        return false;
    }

    public function get($object) {
        foreach ($this->properties as $property) {
            if ($property->canGet() && $property->get($object) !== null) {
                return $property->get($object);
            }
        }
        return null;
    }

    public function set($object, $value) {
        foreach ($this->properties as $property) {
            if ($property->canSet()) {
                $property->set($object, $value);
            }
        }
        return null;
    }

    public function defaultValue() {
        foreach ($this->properties as $property) {
            if ($property->defaultValue()) {
                return $property->defaultValue();
            }
        }
        return null;
    }

    public function add(Property $property) {
        $this->properties[] = $property;
    }

    /**
     * @return Type
     */
    public function type() {
        $types = array();
        foreach ($this->properties as $property) {
            if (!($property->type() instanceof UnknownType)) {
                $types[] = $property->type();
            }
        }
        if (!$types) {
            return new UnknownType();
        }
        $types = array_unique($types);
        if (count($types) == 1) {
            return $types[0];
        }
        return new MultiType($types);
    }

    /**
     * @return string|null
     */
    public function comment() {
        foreach ($this->properties as $property) {
            $comment = $property->comment();
            if ($comment) {
                return $comment;
            }
        }
        return null;
    }
}