<?php

namespace Entity;

class MethodData {
    public $name        = "";
    public $arguments   = [];
    public $doc         = "";
    public $type        = 0;
    public $startLine   = 0;
    public $endLine     = 0;
    public $isStatic    = false;
    public $isFinal     = false;
    public $isAbstract  = false;
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
            if($argument->type){
                $curParam[] = $argument->type;
            }
            $curParam[] = sprintf("$%s", $argument->name);
            $paramsStr[] = implode(" ", $curParam); 
        }
        return implode(", ", $paramsStr);
    }
    public function getReturn(){
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
    public function getArgumentsArr(){
        $map = [];
        foreach($this->arguments AS $argument){
            $type = $argument->type;
            $argName = '$' . $argument->name;
            if($type instanceof FQCN)
                $type = $type->toString();
            $map[$argName] = $type;
        }
        return $map;
    }

    public function toArray(){
        return [
            "params"            => $this->getArgumentsArr(),
            "docComment"        => $this->doc,
            "inheritdoc"        => "",
            "startLine"         => $this->startLine,
            "endLine"           => $this->endLine,
            "origin"            => "",
            "signature"         => $this->getSignature(),
            "return"            => $this->return,
            "array_return"      => 0
        ];
    }
}
