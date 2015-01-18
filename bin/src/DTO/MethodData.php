<?php

namespace DTO;

class MethodData {
    public $name        = "";
    public $arguments   = [];
    public $doc         = "";
    public $modifier    = "";
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
        return "";
    }

    public function toArray(){
        return [
            "params"            => $this->arguments,
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
