<?php

namespace DTO;

class ClassData{
    public $interfaces      = [];
    public $parentClasses   = [];
    public $methods         = [];
    public $properties      = [];
    public $constants       = [];
    public $uses            = [];

    /**
     *
     * @var FQCN
     */
    public $fqcn;
    public $doc             = "";
    public $startLine       = 0;
    public $file            = "";
    public function __construct(FQCN $fqcn, $file){
        $this->fqcn = $fqcn;
        $this->file = $file;
    }
    public function getParentClass(){
        return "";
    }
    public function addMethod(MethodData $method){
        $this->methods[] = $method;
    }
    public function toArray(){
        return [
            "methods"       => $this->methods,
            "namespaces"    => $this->uses,
            "properties"    => $this->properties,
            "constants"     => $this->constants,
            "interfaces"    => $this->interfaces,
            "classname"     => $this->fqcn->className,
            "parentclasses" => $this->parentClasses,
            "docComment"    => $this->doc,
            "startLine"     => $this->startLine,
            "file"          => $this->file,
            "parentclass"   => $this->getParentClass()
        ];
    }
}
