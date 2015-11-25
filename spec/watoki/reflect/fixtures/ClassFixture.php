<?php
namespace spec\watoki\reflect\fixtures;

use watoki\scrut\Fixture;

class ClassFixture extends Fixture {

    public function givenTheClass($fqn) {
        $this->givenTheClass_WithTheBody($fqn, '');
    }

    public function givenTheClass_WithTheBody($fqn, $body, $abstract = false) {
        $this->givenThe__WithTheBody('class', $fqn, $body, $abstract);
    }

    public function givenTheInterface_WithTheBody($fqn, $body, $abstract = false) {
        $this->givenThe__WithTheBody('interface', $fqn, $body, $abstract);
    }

    public function givenThe__WithTheBody($type, $fqn, $body, $abstract = false) {
        $parts = explode('\\', $fqn);
        $name = array_pop($parts);
        $namespace = implode('\\', $parts);

        if (class_exists($fqn)) {
            return;
        }

        $namespaceString = $namespace ? "namespace $namespace;" : '';
        $abstract = $abstract ? 'abstract' : '';

        $this->evalCode("$namespaceString $abstract $type $name {
            $body
        }");
    }

    public function givenTheAbstractClass_WithTheBody($fqn, $body) {
        $this->givenTheClass_WithTheBody($fqn, $body, true);
    }

    public function givenTheClass_Extending_WithTheBody($fqn, $parentClass, $body) {
        $parts = explode('\\', $fqn);
        $name = array_pop($parts);
        $namespace = implode('\\', $parts);

        if (class_exists($fqn)) {
            return;
        }

        $namespaceString = $namespace ? "namespace $namespace;" : '';

        $this->evalCode("$namespaceString class $name extends $parentClass {
            $body
        }");
    }

    public function givenTheClass_Implementing_WithTheBody($fqn, $interface, $body) {
        $parts = explode('\\', $fqn);
        $name = array_pop($parts);
        $namespace = implode('\\', $parts);

        if (class_exists($fqn)) {
            return;
        }

        $namespaceString = $namespace ? "namespace $namespace;" : '';

        $this->evalCode("$namespaceString class $name implements $interface {
            $body
        }");
    }

    private function evalCode($code) {
        $evald = eval($code);
        if (!$evald === false) {
            throw new \Exception("Could not eval: \n\n" . $code);
        }
    }

    public function then_ShouldBe($expression, $value) {
        $this->spec->assertEquals($value, eval("return $expression;"), "Not [" . var_export($value, true) . "]: " . $expression);
    }

    public function givenISetAnInstanceOf_AsSingletonFor($implementation, $interface) {
        $this->spec->factory->setSingleton(new $implementation, $interface);
    }
}