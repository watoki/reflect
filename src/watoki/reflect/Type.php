<?php
namespace watoki\reflect;

interface Type {

    public function is($value);

    public function __toString();
}