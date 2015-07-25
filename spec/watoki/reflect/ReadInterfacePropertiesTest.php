<?php
namespace spec\watoki\reflect;

use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;
use watoki\reflect\TypeFactory;
use watoki\scrut\Specification;

/**
 * @property \spec\watoki\reflect\fixtures\ClassFixture class <-
 */
class ReadInterfacePropertiesTest extends Specification {

    function testFindPublicProperties() {
        $this->class->givenTheClass_WithTheBody('publicProperties\SomeClass', '
            public $public = "one";
            private $private = "four";
            public static $static = "not";
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
            static function getStatic() {}
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
        $this->then_ShouldHaveTheType('unknown', UnknownType::$CLASS);
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
        $this->then_ShouldHaveTheType('one', LongType::$CLASS);
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

    function testOptionalConstructorTypes() {
        $this->class->givenTheClass_WithTheBody('optionalConstructorTypes\SomeClass', '
            function __construct(\DateTime $one = null) {}
        ');
        $this->whenIDetermineThePropertiesOf('optionalConstructorTypes\SomeClass');
        $this->thenThereShouldBe_Properties(1);
        $this->then_ShouldHaveTheType('one', NullableType::$CLASS);
    }

    function testComplexTypes() {
        $this->class->givenTheClass_WithTheBody('ComplexTypes\SomeClass', '
            /** @var null|int */
            public $int;

            /** @var string[] */
            public $array;

            /** @var array|array[]|\DateTime[][] */
            public $deepArray;

            /** @var int|string */
            public $multi;

            /** @var int|int */
            public $notMulti;

            /** @var int[]|string[] */
            public $multiArray;

            /** @var string */
            public $merged;

            /** @return double */
            function getMerged() {}
        ');

        $this->whenIDetermineThePropertiesOf('ComplexTypes\SomeClass');
        $this->thenThereShouldBe_Properties(7);

        $this->then_ShouldHaveTheType('int', NullableType::$CLASS);
        $this->thenTheInnerTypeOf_ShouldBe('int', IntegerType::$CLASS);

        $this->then_ShouldHaveTheType('array', ArrayType::$CLASS);
        $this->thenTheItemTypeOf_ShouldBe('array', StringType::$CLASS);

        $this->then_ShouldHaveTheType('deepArray', ArrayType::$CLASS);
        $this->thenTheItemTypeOf_ShouldBe('deepArray', ArrayType::$CLASS);
        $this->thenTheItemTypeOfTheItemTypeOf_ShouldBe('deepArray', ClassType::$CLASS);

        $this->then_ShouldHaveTheType('multi', MultiType::$CLASS);
        $this->thenTheTypesOf_ShouldBe('multi', array(IntegerType::$CLASS, StringType::$CLASS));

        $this->then_ShouldHaveTheType('notMulti', IntegerType::$CLASS);

        $this->then_ShouldHaveTheType('multiArray', ArrayType::$CLASS);
        $this->thenTheItemTypeOf_ShouldBe('multiArray', MultiType::$CLASS);

        $this->then_ShouldHaveTheType('merged', MultiType::$CLASS);
        $this->thenTheTypesOf_ShouldBe('merged', array(StringType::$CLASS, DoubleType::$CLASS));
    }

    function testDeDuplicateTypes() {
        $this->class->givenTheClass_WithTheBody('deDuplicate\SomeClass', '
            /** @var \deDuplicate\some\OtherClass|null */
            public $one;
            function __construct(some\OtherClass $one = null) {}
            function getOne() {}

            function getUnknown() {}
            function setUnknown($u) {}

            /** @return string */
            function getNullable() {}
            /** @param string $s */
            function setNullable($s = null) {}
        ');
        $this->class->givenTheClass('deDuplicate\some\OtherClass');
        $this->class->givenTheClass_WithTheBody('deDuplicate\some\OtherClassId', 'function __toString() { return "foo"; }');

        $this->whenIDetermineThePropertiesOf('deDuplicate\SomeClass');
        $this->thenThereShouldBe_Properties(3);
        $this->then_ShouldHaveTheType('one', NullableType::$CLASS);
        $this->thenTheInnerTypeOf_ShouldBe('one', ClassType::$CLASS);

        $this->then_ShouldHaveTheType('unknown', UnknownType::$CLASS);

        $this->then_ShouldHaveTheType('nullable', MultiType::$CLASS);
    }

    function testDocComment() {
        $this->class->givenTheClass_WithTheBody('docComments\SomeClass', '
            /**
             * @var string Some comment
             */
            public $public;

            /**
             * @param string $one Comment One
             * @param string $two Comment Two
             */
            function __construct($one, $two) {}

            /**
             * @return string A comment as well
             */
            function getGetter() {}

            /**
             * @param $g Ignored
             */
            function setGetter($g) {}

            /**
             * @param $a And this too
             */
            function setSetter($a) {}
        ');

        $this->givenTheConstructorArgument_Is('one', '1');
        $this->givenTheConstructorArgument_Is('two', '2');

        $this->whenIDetermineThePropertiesOf('docComments\SomeClass');
        $this->thenThereShouldBe_Properties(5);

        $this->then_ShouldHaveTheComment('public', "Some comment");
        $this->then_ShouldHaveTheComment('one', "Comment One");
        $this->then_ShouldHaveTheComment('two', "Comment Two");
        $this->then_ShouldHaveTheComment('getter', "A comment as well");
        $this->then_ShouldHaveTheComment('setter', "And this too");
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
        $reader = new PropertyReader(new TypeFactory(), $class);
        $this->properties = $reader->readInterface($this->object);
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

    private function then_ShouldHaveTheComment($name, $comment) {
        $this->assertEquals($this->properties[$name]->comment(), $comment);
    }

    private function then_ShouldHaveTheType($name, $type) {
        $this->assertInstanceOf($type, $this->properties[$name]->type());
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

    private function thenTheItemTypeOfTheItemTypeOf_ShouldBe($name, $expectedType) {
        $type = $this->properties[$name]->type();
        if (!($type instanceof ArrayType)) {
            $this->fail("Not an ArrayType: $name");
        }
        $itemType = $type->getItemType();
        if (!($itemType instanceof ArrayType)) {
            $this->fail("Item Type not an ArrayType: $name");
        }
        $this->assertInstanceOf($expectedType, $itemType->getItemType());
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

} 