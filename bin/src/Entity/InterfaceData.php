<?php

namespace Entity;

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
        $this->methods = new MethodsCollection;
    }
    public function addMethod(MethodData $method){
        $this->methods->add($method);
    }
    public function toArray(){
        return [
            "methods"       => $this->methods->toArray(),
            "namespaces"    => $this->uses,
            "constants"     => $this->constants,
            "interfaces"    => $this->interfaces,
            "classname"     => $this->fqcn->className,
            "docComment"    => $this->doc,
            "startLine"     => $this->startLine,
            "file"          => $this->file
        ];
    }
}
