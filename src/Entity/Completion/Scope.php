<?php

namespace Entity\Completion;

use Entity\FQCN;
use Entity\Node\Variable;
use Entity\Node\Uses;

class Scope {
    private $vars           = [];
    private $functions      = [];
    private $fqcn;
    private $uses;
    private $parent;

    public function __construct(Scope $parent = null){
        if($parent){
            $this->setParent($parent);
        }
    }
    public function addVar(Variable $var){
        $this->vars[$var->getName()] = $var;
    }
    public function getVars(){
        return $this->vars;
    }
    public function getVar($varName){
        if(array_key_exists($varName, $this->vars)){
            return $this->vars[$varName];
        }
        return null;
    }
    public function setParent(Scope $parent){
        $this->parent = $parent;
        $this->setUses($parent->getUses());
        $this->setFQCN($parent->getFQCN());
    }
    public function getParent(){
        return $this->parent;
    }
    public function setFQCN(FQCN $fqcn = null){
        $this->fqcn = $fqcn;
    }
    public function getFQCN(){
        return $this->fqcn;
    }
    public function setUses(Uses $uses = null){
        $this->uses = $uses;
        if($this->parent instanceof Scope){
            $this->parent->setUses($uses);
        }
    }
    public function getUses(){
        return $this->uses;
    }
}
