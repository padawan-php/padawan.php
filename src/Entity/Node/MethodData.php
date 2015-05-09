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
            $this->getParamsStr(), $this->getReturnStr()
        );
    }

    public function addParam(MethodParam $param){
        if(array_key_exists($param->getName(), $this->arguments)){
            $var = $this->arguments[$param->getName()];
            if(empty($param->getType())){
                $param->setType($var->getType());
            }
        }
        $this->arguments[$param->getName()] = $param;
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
    public function getReturnStr(){
        if($this->return instanceof FQCN){
            return $this->return->toString();
        }
        return "mixed";
    }
    public function getReturn(){
        return $this->return;
    }
    public function setReturn(FQCN $fqcn = null){
        $this->return = $fqcn;
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
    public function isMagic(){
        return in_array($this->name, [
            '__construct',
            '__destruct',
            '__get',
            '__set',
            '__isset',
            '__unset',
            '__toString',
            '__call',
            '__clone'
        ]);
    }
    public function setType($type){
        $this->type = $type;
    }
}
