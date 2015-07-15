<?php
namespace spec\watoki\reflect;

use watoki\reflect\type\ArrayType;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\DoubleType;
use watoki\reflect\type\FloatType;
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

        $this->assertEquals($type->__toString(), 'boolean');
    }

    function testDouble() {
        $type = new DoubleType();
        $this->assertTrue($type->is(0.0));
        $this->assertFalse($type->is(0));

        $this->assertEquals($type->__toString(), 'double');
    }

    function testFloat() {
        $type = new FloatType();
        $this->assertTrue($type->is(0.0));
        $this->assertFalse($type->is(0));

        $this->assertEquals($type->__toString(), 'float');
    }

    function testLong() {
        $type = new LongType();
        $this->assertTrue($type->is(0));
        $this->assertFalse($type->is(0.0));

        $this->assertEquals($type->__toString(), 'long');
    }

    function testInteger() {
        $type = new IntegerType();
        $this->assertTrue($type->is(0));
        $this->assertFalse($type->is(0.0));

        $this->assertEquals($type->__toString(), 'integer');
    }

    function testString() {
        $type = new StringType();
        $this->assertTrue($type->is("foo"));
        $this->assertFalse($type->is(0));

        $this->assertEquals($type->__toString(), 'string');
    }

    function testArray() {
        $type = new ArrayType(new BooleanType());
        $this->assertTrue($type->is(array()));
        $this->assertTrue($type->is(array(true, false)));
        $this->assertFalse($type->is(array(true, 0)));

        $this->assertEquals($type->__toString(), 'boolean[]');
    }

    function testClass() {
        $type = new ClassType(ValidateTypeTest_Foo::$CLASS);
        $this->assertTrue($type->is(new ValidateTypeTest_Foo()));
        $this->assertTrue($type->is(new ValidateTypeTest_Bar()));
        $this->assertFalse($type->is(new \StdClass()));
        $this->assertFalse($type->is("foo"));

        $this->assertEquals($type->__toString(), ValidateTypeTest_Foo::$CLASS);
    }

    function testMulti() {
        $type = new MultiType(array(new StringType(), new BooleanType()));
        $this->assertTrue($type->is("foo"));
        $this->assertTrue($type->is(true));
        $this->assertFalse($type->is(0));

        $this->assertEquals($type->__toString(), 'string|boolean');
    }

    function testNullable() {
        $type = new NullableType(new StringType());
        $this->assertTrue($type->is(null));
        $this->assertTrue($type->is("foo"));
        $this->assertFalse($type->is(0));

        $this->assertEquals($type->__toString(), 'null|string');
    }

    function testNull() {
        $type = new NullType();
        $this->assertTrue($type->is(null));
        $this->assertFalse($type->is("foo"));

        $this->assertEquals($type->__toString(), 'null');
    }

    function testUnknown() {
        $type = new UnknownType('foo bar');
        $this->assertTrue($type->is(null));
        $this->assertTrue($type->is("foo"));
        $this->assertTrue($type->is(0));

        $this->assertEquals($type->__toString(), 'foo bar');
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