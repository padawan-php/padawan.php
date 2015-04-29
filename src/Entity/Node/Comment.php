<?php

namespace Entity\Node;

use Entity\FQCN;

class Comment {
    private $return;
    private $doc         = "";
    private $vars        = [];
    private $throws      = [];
    public function __construct($doc){
        $this->doc = $doc;
    }
    public function addVar(Variable $var){
        $this->vars[$var->getName()] = $var;
    }
    public function getVars(){
        return $this->vars;
    }
    public function setReturn(FQCN $fqcn){
        $this->return = $fqcn;
    }
    public function getReturn(){
        return $this->return;
    }
    public function getDoc(){
        return $this->doc;
    }
}
