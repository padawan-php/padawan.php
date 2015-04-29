<?php

namespace Entity\Node;

use Entity\FQCN;

class MethodData {
    public $name        = "";
    public $arguments   = [];
    public $doc         = "";
    public $type        = 0;
    public $startLine   = 0;
    public $endLine     = 0;
    public $return      = "";

    public function __construct($name){
        $this->name = $name;
    }

    public function getSignature(){
        return sprintf("(%s) : %s",
            $this->getParamsStr(), $this->getReturn()
        );
    }

    public function addParam(MethodParam $param){
        $this->arguments[] = $param;
    }
    public function getParamsStr(){
        $paramsStr = [];
        foreach($this->arguments as $argument){
            $curParam = [];
            if($argument->getType()){
                $curParam[] = $argument->getType();
            }
            $curParam[] = sprintf("$%s", $argument->getName());
            $paramsStr[] = implode(" ", $curParam);
        }
        return implode(", ", $paramsStr);
    }
    public function getReturn(){
        if($this->return instanceof FQCN){
            return $this->return->toString();
        }
        return "none";
    }
    public function isPublic() {
        return (bool) ($this->type & ClassData::MODIFIER_PUBLIC);
    }

    public function isProtected() {
        return (bool) ($this->type & ClassData::MODIFIER_PROTECTED);
    }

    public function isPrivate() {
        return (bool) ($this->type & ClassData::MODIFIER_PRIVATE);
    }

    public function isAbstract() {
        return (bool) ($this->type & ClassData::MODIFIER_ABSTRACT);
    }

    public function isFinal() {
        return (bool) ($this->type & ClassData::MODIFIER_FINAL);
    }

    public function isStatic() {
        return (bool) ($this->type & ClassData::MODIFIER_STATIC);
    }
    public function setType($type){
        $this->type = $type;
    }
}
