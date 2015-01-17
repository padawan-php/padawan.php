<?php

namespace DTO;

class FQCN {
    public $className;
    public $namespace;
    public function __construct($className, $namespace = ""){
        $this->className = $className;
        $this->namespace = $namespace;
    }
    public function toString(){
        return $this->namespace . '\\' . $this->className;
    }
    public function __toString(){
        return $this->toString();
    }
}
