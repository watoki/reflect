<?php
namespace spec\watoki\reflect;

use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;
use watoki\scrut\Specification;

/**
 * @property \spec\watoki\reflect\fixtures\ClassFixture class <-
 */
class ReadStatePropertiesTest extends Specification {

    protected function background() {
        $this->class->givenTheClass_WithTheBody('stateProperties\SomeClass', '
            static public $static;
            private $private;
            protected $protected;
            public $public;

            function __construct($private, $protected, $public) {
                $this->private = $private;
                $this->protected = $protected;
                $this->public = $public;
            }
        ');
    }

    function testFindAllNonStaticProperties() {
        $this->whenIReadTheStatePropertiesOf('stateProperties\SomeClass');
        $this->thenThereShouldBe_Properties(3);
    }

    function testAccessPrivateProperties() {
        $this->givenAnInstanceOf_With('stateProperties\SomeClass', array('a private', 'a protected', 'a public'));

        $this->whenIReadTheStatePropertiesOf('stateProperties\SomeClass');
        $this->thenThereShouldBe_Properties(3);
        $this->thenTheValueOfProperty_ShouldBe('private', 'a private');
        $this->thenTheValueOfProperty_ShouldBe('protected', 'a protected');
        $this->thenTheValueOfProperty_ShouldBe('public', 'a public');

        $this->whenISet_To('private', 'not so private');
        $this->thenTheValueOfProperty_ShouldBe('private', 'not so private');
    }

    function testUseFilterToIgnorePrivateProperties() {
        $this->whenIReadTheStatePropertiesOf_WithTheFilter('stateProperties\SomeClass',
            ~\ReflectionProperty::IS_PRIVATE);
        $this->thenThePropertiesShouldBe(array('protected', 'public'));
    }

    ##########################################################################################

    /** @var \watoki\collections\Map|\watoki\reflect\Property[] */
    private $properties;

    private $instance;

    private function givenAnInstanceOf_With($class, $arguments) {
        $reflection = new \ReflectionClass($class);
        $this->instance = $reflection->newInstanceArgs($arguments);
    }

    private function whenIReadTheStatePropertiesOf($class) {
        $this->whenIReadTheStatePropertiesOf_WithTheFilter($class, null);
    }

    private function whenISet_To($name, $value) {
        $this->properties[$name]->set($this->instance, $value);
    }

    private function whenIReadTheStatePropertiesOf_WithTheFilter($class, $filter) {
        $reader = new PropertyReader(new TypeFactory(), $class);
        $this->properties = $reader->readState($filter);
    }

    private function thenThereShouldBe_Properties($int) {
        $this->assertCount($int, $this->properties);
    }

    private function thenTheValueOfProperty_ShouldBe($name, $value) {
        $this->assertEquals($value, $this->properties[$name]->get($this->instance));
    }

    private function thenThePropertiesShouldBe($array) {
        $this->assertEquals($array, $this->properties->keys()->toArray());
    }

} 