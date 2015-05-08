<?php

namespace Entity\Node;

use Entity\FQCN;
use Entity\Collection\MethodsCollection;

class InterfaceData {
    public $fqcn;
    public $interfaces      = [];
    public $constants       = [];
    public $uses            = [];
    public $methods;
    public $file            = "";
    public $startLine       = 0;
    public $doc             = "";
    public function __construct(FQCN $fqcn, $file){
        $this->fqcn = $fqcn;
        $this->file = $file;
        $this->methods = new MethodsCollection($this);
    }
    public function addMethod(MethodData $method){
        $this->methods->add($method);
    }
    public function getInterfaces(){
        return $this->interfaces;
    }
    public function addInterface($interface){
        $fqcn = $interface;
        if($interface instanceof InterfaceData){
            $fqcn = $interface->fqcn;
        }
        $this->interfaces[$fqcn->toString()] = $interface;
    }
}
