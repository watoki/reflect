<?php
namespace spec\watoki\reflect;

use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IdentifierObjectType;
use watoki\reflect\type\IdentifierType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;
use watoki\scrut\Specification;

/**
 * @property \spec\watoki\qrator\fixtures\ClassFixture class <-
 */
class DeterminePropertiesOfObjectTest extends Specification {

    function testFindPublicProperties() {
        $this->class->givenTheClass_WithTheBody('publicProperties\SomeClass', '
            public $public = "one";
            private $private = "four";
        ');

        $this->whenIDetermineThePropertiesOf('publicProperties\SomeClass');
        $this->thenThereShouldBe_Properties(1);
        $this->then_ShouldBeGettable('public');
        $this->then_ShouldBeSettable('public');
        $this->thenTheValueOf_ShouldBe('public', 'one');
    }

    function testFindAccessorProperties() {
        $this->class->givenTheClass_WithTheBody('accessors\SomeClass', '
            function getGetter() { return "seven"; }
            function isBoolean() { return true; }
            function setSetter($a) { }
            function getBoth() {}
            function setBoth($a) {}
            function notAnAccessor() {}
            function getNeither ($becauseOfTheParameter) {}
        ');

        $this->whenIDetermineThePropertiesOf('accessors\SomeClass');
        $this->thenThereShouldBe_Properties(4);
        $this->then_ShouldBeGettable('getter');
        $this->then_ShouldNotBeSettable('getter');
        $this->then_ShouldBeSettable('setter');
        $this->then_ShouldNotBeGettable('setter');
        $this->then_ShouldBeGettable('boolean');
        $this->thenTheValueOf_ShouldBe('getter', 'seven');
        $this->thenTheValueOf_ShouldBe('boolean', true);
    }

    function testPublicTrumpAccessor() {
        $this->class->givenTheClass_WithTheBody('both\SomeClass', '
            public $publicAndGetter = "public";
            function getPublicAndGetter() { return "getter"; }
        ');

        $this->whenIDetermineThePropertiesOf('both\SomeClass');
        $this->thenThereShouldBe_Properties(1);
        $this->thenTheValueOf_ShouldBe('publicAndGetter', 'public');
    }

    function testFindConstructorProperties() {
        $this->class->givenTheClass_WithTheBody('constructor\ClassWithConstructor', '
            function __construct($one = null) {}
        ');

        $this->whenIDetermineThePropertiesOf('constructor\ClassWithConstructor');
        $this->thenThereShouldBe_Properties(1);
        $this->then_ShouldBeSettable('one');
        $this->then_ShouldNotBeGettable('one');
    }

    function testMergeProperties() {
        $this->class->givenTheClass_WithTheBody('mergeProperties\SomeClass', '
            public $one;
            function __construct($one = null, $two = null) {}
            function getTwo() {}
        ');

        $this->whenIDetermineThePropertiesOf('mergeProperties\SomeClass');
        $this->thenThereShouldBe_Properties(2);
        $this->then_ShouldBeGettable('one');
        $this->then_ShouldBeGettable('two');
    }

    function testRequiredProperties() {
        $this->class->givenTheClass_WithTheBody('required\SomeClass', '
            public $two;
            public $four;
            function __construct($one, $two, $three = null) {}
        ');
        $this->givenTheConstructorArgument_Is('one', 'uno');
        $this->givenTheConstructorArgument_Is('two', 'dos');

        $this->whenIDetermineThePropertiesOf('required\SomeClass');
        $this->thenThereShouldBe_Properties(4);

        $this->then_ShouldBeRequired('one');
        $this->then_ShouldBeRequired('two');
        $this->then_ShouldBeOptional('three');
        $this->then_ShouldBeOptional('four');
    }

    function testPublicPropertyTypes() {
        $this->class->givenTheClass_WithTheBody('publicTypes\SomeClass', '
            /** @var int */
            public $int;

            /** @var string */
            public $string;

            /** @var \DateTime */
            public $class;

            public $unknown;
        ');

        $this->whenIDetermineThePropertiesOf('publicTypes\SomeClass');
        $this->thenThereShouldBe_Properties(4);
        $this->then_ShouldHaveTheType('int', IntegerType::$CLASS);
        $this->then_ShouldHaveTheType('string', StringType::$CLASS);
        $this->then_ShouldHaveTheType('class', ClassType::$CLASS);
        $this->thenTheClassOf_ShouldBe('class', 'DateTime');
        $this->then_ShouldHaveNoType('unknown');
    }

    function testAccessorTypes() {
        $this->class->givenTheClass_WithTheBody('accessorTypes\SomeClass', '
            /** @return long */
            function getOne() {}

            /** @param float $two */
            function setTwo($two) {}

            function setThree(\DateTime $three) {}
        ');

        $this->whenIDetermineThePropertiesOf('accessorTypes\SomeClass');
        $this->thenThereShouldBe_Properties(3);
        $this->then_ShouldHaveTheType('one', IntegerType::$CLASS);
        $this->then_ShouldHaveTheType('two', FloatType::$CLASS);
        $this->then_ShouldHaveTheType('three', ClassType::$CLASS);
    }

    function testConstructorTypes() {
        $this->class->givenTheClass_WithTheBody('constructorTypes\SomeClass', '
            /** @param integer $one */
            function __construct($one, \DateTime $two) {}
        ');
        $this->givenTheConstructorArgument_Is('one', 'uno');
        $this->givenTheConstructorArgument_Is('two', new \DateTime());

        $this->whenIDetermineThePropertiesOf('constructorTypes\SomeClass');
        $this->thenThereShouldBe_Properties(2);
        $this->then_ShouldHaveTheType('one', IntegerType::$CLASS);
        $this->then_ShouldHaveTheType('two', ClassType::$CLASS);
    }

    function testComplexTypes() {
        $this->class->givenTheClass_WithTheBody('ComplexTypes\SomeClass', '
            /** @var null|int */
            public $int;

            /** @var array|string[] */
            public $array;

            /** @var int|string */
            public $multi;

            /** @var string */
            public $merged;

            /** @return double */
            function getMerged() {}
        ');

        $this->whenIDetermineThePropertiesOf('ComplexTypes\SomeClass');
        $this->thenThereShouldBe_Properties(4);

        $this->then_ShouldHaveTheType('int', NullableType::$CLASS);
        $this->thenTheInnerTypeOf_ShouldBe('int', IntegerType::$CLASS);

        $this->then_ShouldHaveTheType('array', ArrayType::$CLASS);
        $this->thenTheItemTypeOf_ShouldBe('array', StringType::$CLASS);

        $this->then_ShouldHaveTheType('multi', MultiType::$CLASS);
        $this->thenTheTypesOf_ShouldBe('multi', array(IntegerType::$CLASS, StringType::$CLASS));

        $this->then_ShouldHaveTheType('merged', MultiType::$CLASS);
        $this->thenTheTypesOf_ShouldBe('merged', array(StringType::$CLASS, DoubleType::$CLASS));
    }

    function testIdentifierType() {
        $this->class->givenTheClass_WithTheBody('IdentifierType\SomeEntity', 'public static $CLASS = __CLASS__;');
        $this->class->givenTheClass_WithTheBody('IdentifierType\SomeEntityId', 'function __toString() {}');
        $this->class->givenTheClass_WithTheBody('IdentifierType\elsewhere\ThisId', '
            const TARGET = "IdentifierType\SomeEntity";
            function __toString() {}
        ');
        $this->class->givenTheClass_WithTheBody('IdentifierType\elsewhere\ThatId', '
            public static function TARGET() { return \IdentifierType\SomeEntity::$CLASS; }
            function __toString() {}
        ');
        $this->class->givenTheClass_WithTheBody('IdentifierType\SomeClass', '
            /** @var SomeEntity-ID */
            public $suffixed;

            /** @var SomeEntity-Id */
            public $caseInsensitiveSuffix;

            /** @var \IdentifierType\elsewhere\ThisId */
            public $targetConst;

            /** @var \IdentifierType\elsewhere\ThatId */
            public $targetStaticMethod;

            /** @var SomeEntityId */
            public $sameNameSpace;

            function __construct(SomeEntityId $inConstructor = null) {}
        ');

        $this->whenIDetermineThePropertiesOf('IdentifierType\SomeClass');
        $this->thenThereShouldBe_Properties(6);

        $this->then_ShouldBeAndIdentifierFor('suffixed', 'IdentifierType\SomeEntity');
        $this->then_ShouldBeAndIdentifierFor('caseInsensitiveSuffix', 'IdentifierType\SomeEntity');
        $this->then_ShouldBeAndIdentifierObjectFor('targetConst', 'IdentifierType\SomeEntity');
        $this->then_ShouldBeAndIdentifierObjectFor('targetStaticMethod', 'IdentifierType\SomeEntity');
        $this->then_ShouldBeAndIdentifierObjectFor('sameNameSpace', 'IdentifierType\SomeEntity');
        $this->then_ShouldBeAndIdentifierObjectFor('inConstructor', 'IdentifierType\SomeEntity');
    }

    ##################################################################################################

    private $args = array();

    private $object;

    /** @var Property[] */
    private $properties;

    protected function setUp() {
        parent::setUp();
    }

    private function whenIDetermineThePropertiesOf($class) {
        $reflection = new \ReflectionClass($class);
        $this->object = $reflection->newInstanceArgs($this->args);
        $reader = new PropertyReader($class);
        $this->properties = $reader->readProperties($this->object);
    }

    private function thenThereShouldBe_Properties($int) {
        $this->assertCount($int, $this->properties);
    }

    private function thenTheValueOf_ShouldBe($name, $value) {
        $this->assertEquals($value, $this->properties[$name]->get($this->object));
    }

    private function then_ShouldNotBeGettable($name) {
        $this->assertFalse($this->properties[$name]->canGet(), "$name should not be gettable");
    }

    private function then_ShouldBeSettable($name) {
        $this->assertTrue($this->properties[$name]->canSet(), "$name should be settable");
    }

    private function then_ShouldNotBeSettable($name) {
        $this->assertFalse($this->properties[$name]->canSet(), "$name should not be settable");
    }

    private function then_ShouldBeGettable($name) {
        $this->assertTrue($this->properties[$name]->canGet(), "$name should be gettable");
    }

    private function givenTheConstructorArgument_Is($key, $value) {
        $this->args[$key] = $value;
    }

    private function then_ShouldBeRequired($name) {
        $this->assertTrue($this->properties[$name]->isRequired(), "$name should be required");
    }

    private function then_ShouldBeOptional($name) {
        $this->assertFalse($this->properties[$name]->isRequired(), "$name should be optional");
    }

    private function then_ShouldHaveTheType($name, $type) {
        $this->assertInstanceOf($type, $this->properties[$name]->type());
    }

    private function then_ShouldHaveNoType($name) {
        $this->assertEquals(null, $this->properties[$name]->type());
    }

    private function thenTheClassOf_ShouldBe($name, $class) {
        $type = $this->properties[$name]->type();
        if (!($type instanceof ClassType)) {
            $this->fail("Not a ClassType: $name");
        }
        $this->assertEquals($class, $type->getClass());
    }

    private function thenTheInnerTypeOf_ShouldBe($name, $expectedType) {
        $type = $this->properties[$name]->type();
        if (!($type instanceof NullableType)) {
            $this->fail("Not a NullableType: $name");
        }
        $this->assertInstanceOf($expectedType, $type->getType());
    }

    private function thenTheItemTypeOf_ShouldBe($name, $expectedType) {
        $type = $this->properties[$name]->type();
        if (!($type instanceof ArrayType)) {
            $this->fail("Not an ArrayType: $name");
        }
        $this->assertInstanceOf($expectedType, $type->getItemType());
    }

    private function thenTheTypesOf_ShouldBe($name, $types) {
        $type = $this->properties[$name]->type();
        if (!($type instanceof MultiType)) {
            $this->fail("Not an MultiType: $name");
        }
        $this->assertEquals($types, array_map(function ($type) {
            return get_class($type);
        }, $type->getTypes()));
    }

    private function thenTheTargetOf_ShouldBe($name, $class) {
        $type = $this->properties[$name]->type();
        if (!($type instanceof IdentifierType)) {
            $this->fail("Not a IdentifierType: $name");
        }
        $this->assertEquals($class, $type->getTarget());
    }

    private function then_ShouldBeAndIdentifierFor($property, $target) {
        $this->then_ShouldHaveTheType($property, IdentifierType::$CLASS);
        $this->thenTheTargetOf_ShouldBe($property, $target);
    }

    private function then_ShouldBeAndIdentifierObjectFor($property, $target) {
        $this->then_ShouldHaveTheType($property, IdentifierObjectType::$CLASS);
        $this->thenTheTargetOf_ShouldBe($property, $target);
    }

} 