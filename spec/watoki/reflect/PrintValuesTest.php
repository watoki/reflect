<?php
namespace spec\watoki\reflect;

use watoki\reflect\ValuePrinter;
use watoki\scrut\Specification;

class PrintValuesTest extends Specification {

    function testPrintPrimitives() {
        $this->assertEquals("NULL", ValuePrinter::serialize(null));
        $this->assertEquals("TRUE", ValuePrinter::serialize(true));
        $this->assertEquals("FALSE", ValuePrinter::serialize(false));
        $this->assertEquals("12.54", ValuePrinter::serialize(12.54));
        $this->assertEquals("'Hello'", ValuePrinter::serialize('Hello'));
    }

    function testPrintArrays() {
        $this->assertEquals("[1, 2, 'foo']", ValuePrinter::serialize(array(1, 2, 'foo')));
        $this->assertEquals("['foo': 'bar', 0: 'bas']", ValuePrinter::serialize(array('foo' => 'bar', 'bas')));
        $this->assertEquals("[[1, [2]], 3]", ValuePrinter::serialize(array(array(1, array(2)), 3)));
    }

    function testPrintObject() {
        $this->assertEquals("<stdClass>", ValuePrinter::serialize(new \stdClass()));
        $this->assertEquals("<stdClass>", ValuePrinter::serialize(json_decode('{"foo":"bar"}')));

        $className = "PrintableObject";
        eval("class {$className} { function __toString() { return 'foo'; }}");
        $this->assertEquals("<PrintableObject>('foo')", ValuePrinter::serialize(new $className()));
    }

    function printDateTime() {
        $this->assertEquals("<DateTime>(2011-12-13T14:15:16+00:00)", ValuePrinter::serialize(new \DateTime('2011-12-13 14:15:16 UTC')));
    }

    function testPrintTraversable() {
        $stack = new \SplStack();
        $stack->push('foo');
        $stack->push('bar');

        $this->assertEquals("<SplStack>['bar', 'foo']", ValuePrinter::serialize($stack));
    }

    function testPrintException() {
        $this->assertEquals("<Exception>", ValuePrinter::serialize(new \Exception()));
        $this->assertEquals("<InvalidArgumentException>('Some message')", ValuePrinter::serialize(new \InvalidArgumentException('Some message')));
        $this->assertEquals("<Exception>('With code', 42)", ValuePrinter::serialize(new \Exception('With code', 42)));
        $this->assertEquals("<Exception>('With previous') -> <Exception>('The previous') -> <Exception>", ValuePrinter::serialize(
            new \Exception('With previous', 0,
                new \Exception('The previous', 0,
                    new \Exception()))));
    }
}