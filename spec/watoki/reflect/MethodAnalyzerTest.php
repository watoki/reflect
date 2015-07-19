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

    function testGetType() {
        $first = new MethodAnalyzer(new \ReflectionMethod(MethodAnalyzerTest_Foo::$CLASS, 'first'));
        $this->assertEquals($first->getTypes(new TypeFactory()), [
            'one' => new UnknownType()
        ]);

        $second = new MethodAnalyzer(new \ReflectionMethod(MethodAnalyzerTest_Foo::$CLASS, 'second'));
        $this->assertEquals($second->getTypes(new TypeFactory()), [
            'one' => new StringType(),
            'two' => new NullableType(new ClassType('DateTime')),
        ]);
    }
}

class MethodAnalyzerTest_Foo {
    public static $CLASS = __CLASS__;

    function first($one) {}

    /**
     * @param string $one
     * @param null|\DateTime $two
     */
    function second($one, $two = null) {}
}