<?php
namespace spec\watoki\reflect;

use watoki\reflect\MethodAnalyzer;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;
use watoki\reflect\TypeFactory;
use watoki\scrut\Specification;

class MethodAnalyzerTest extends Specification {

    function testUnknownType() {
        $method = new MethodAnalyzer(new \ReflectionMethod(MethodAnalyzerTest_Foo::$CLASS, 'first'));
        $this->assertEquals($method->getTypes(new TypeFactory()), array(
            'one' => new UnknownType()
        ));
    }

    function testPrimitiveAndClassType() {
        $method = new MethodAnalyzer(new \ReflectionMethod(MethodAnalyzerTest_Foo::$CLASS, 'second'));
        $this->assertEquals($method->getTypes(new TypeFactory()), array(
            'one' => new StringType(),
            'two' => new ClassType('DateTime'),
            'three' => new ClassType('DateTime'),
        ));
    }

    function testNullableTypes() {
        $method = new MethodAnalyzer(new \ReflectionMethod(MethodAnalyzerTest_Foo::$CLASS, 'third'));
        $this->assertEquals($method->getTypes(new TypeFactory()), array(
            'one' => new NullableType(new UnknownType()),
            'two' => new NullableType(new StringType()),
            'three' => new NullableType(new ClassType('DateTime')),
        ));
    }

    function testReadComment() {
        $method = new MethodAnalyzer(new \ReflectionMethod(MethodAnalyzerTest_Foo::$CLASS, 'fourth'));
        $this->assertEquals($method->getComments(), array(
            'one' => null,
            'two' => 'With Comment'
        ));
    }
}

class MethodAnalyzerTest_Foo {
    public static $CLASS = __CLASS__;

    function first($one) {}

    /**
     * @param string $one
     * @param \DateTime $two
     */
    function second($one, $two, /** @noinspection PhpDocSignatureInspection */ \DateTime $three) {}

    /**
     * @param string $two
     * @param \DateTime|null $three
     */
    function third(/** @noinspection PhpDocSignatureInspection */ $one = null, $two = null, \DateTime $three = null) {}

    /**
     * @param string $one
     * @param string $two With Comment
     */
    function fourth($one, $two) {}
}