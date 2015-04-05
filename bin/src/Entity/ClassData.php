<?php

namespace Entity;

class ClassData{
    const MODIFIER_PUBLIC    =  1;
    const MODIFIER_PROTECTED =  2;
    const MODIFIER_PRIVATE   =  4;
    const MODIFIER_STATIC    =  8;
    const MODIFIER_ABSTRACT  = 16;
    const MODIFIER_FINAL     = 32;
    public $interfaces      = [];
    public $parentClasses   = [];
    public $methods;
    public $properties;
    public $constants       = [];
    /** @var Uses */
    public $uses;

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
        $this->methods = new MethodsCollection();
        $this->properties = new PropertiesCollection();
    }
    public function getParentClass(){
        return "";
    }
    public function setParentClass($parentClass){
        if(!in_array($parentClass, $this->parentClasses)){
            array_unshift($this->parentClasses, $parentClass);
        }
    }
    public function addInterface($interface){
        if(!in_array($interface, $this->interfaces)){
            $this->interfaces[] = $interface;
        }
    }
    public function addMethod(MethodData $method){
        $this->methods->add($method);
    }
    public function addProp(ClassProperty $prop){
        $this->properties->add($prop);
    }
    public function addConst($constName){
        if(!in_array($constName, $this->constants)){
            $this->constants[] = $constName;
        }
    }
    public function toArray(){
        return [
            "methods"       => $this->methods->toArray(),
            "namespaces"    => $this->uses->toArray(),
            "properties"    => $this->properties->toArray(),
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
