<?php
namespace spec\watoki\reflect;

use watoki\reflect\type\ArrayType;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IdentifierObjectType;
use watoki\reflect\type\IdentifierType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\LongType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\NullType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;
use watoki\scrut\Specification;

class ValidateTypeTest extends Specification {

    function testBoolean() {
        $type = new BooleanType();
        $this->assertTrue($type->is(true));
        $this->assertTrue($type->is(false));
        $this->assertFalse($type->is(0));
    }

    function testDouble() {
        $type = new DoubleType();
        $this->assertTrue($type->is(0.0));
        $this->assertFalse($type->is(0));
    }

    function testFloat() {
        $type = new FloatType();
        $this->assertTrue($type->is(0.0));
        $this->assertFalse($type->is(0));
    }

    function testLong() {
        $type = new LongType();
        $this->assertTrue($type->is(0));
        $this->assertFalse($type->is(0.0));
    }

    function testInteger() {
        $type = new IntegerType();
        $this->assertTrue($type->is(0));
        $this->assertFalse($type->is(0.0));
    }

    function testString() {
        $type = new StringType();
        $this->assertTrue($type->is("foo"));
        $this->assertFalse($type->is(0));
    }

    function testArray() {
        $type = new ArrayType(new BooleanType());
        $this->assertTrue($type->is(array()));
        $this->assertTrue($type->is(array(true, false)));
        $this->assertFalse($type->is(array(true, 0)));
    }

    function testClass() {
        $type = new ClassType(ValidateTypeTest_Foo::$CLASS);
        $this->assertTrue($type->is(new ValidateTypeTest_Foo()));
        $this->assertTrue($type->is(new ValidateTypeTest_Bar()));
        $this->assertFalse($type->is(new \StdClass()));
        $this->assertFalse($type->is("foo"));
    }

    function testIdentifier() {
        $type = new IdentifierType("Foo", new StringType());
        $this->assertTrue($type->is("foo"));
    }

    function testIdentifierObject() {
        $type = new IdentifierObjectType("Foo", ValidateTypeTest_Foo::$CLASS);
        $this->assertTrue($type->is(new ValidateTypeTest_Foo()));
    }

    function testMulti() {
        $type = new MultiType(array(new StringType(), new BooleanType()));
        $this->assertTrue($type->is("foo"));
        $this->assertTrue($type->is(true));
        $this->assertFalse($type->is(0));
    }

    function testNullable() {
        $type = new NullableType(new StringType());
        $this->assertTrue($type->is(null));
        $this->assertTrue($type->is("foo"));
        $this->assertFalse($type->is(0));
    }

    function testNull() {
        $type = new NullType();
        $this->assertTrue($type->is(null));
        $this->assertFalse($type->is("foo"));
    }

    function testUnknown() {
        $type = new UnknownType();
        $this->assertTrue($type->is(null));
        $this->assertTrue($type->is("foo"));
        $this->assertTrue($type->is(0));
    }
}

class ValidateTypeTest_Foo {
    public static $CLASS = __CLASS__;

    function __toString() {
        return "foo";
    }

}

class ValidateTypeTest_Bar extends ValidateTypeTest_Foo {
    public static $CLASS = __CLASS__;
}