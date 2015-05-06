<?php

namespace Entity\Node;

use Entity\FQCN;

class Comment {

    public function __construct($doc){
        $this->doc = $doc;
    }
    public function addVar(Variable $var){
        $this->vars[$var->getName()] = $var;

    }
    public function addProperty(ClassProperty $prop){
        $this->properties[$prop->name] = $prop;
    }
    public function getVars(){
        return $this->vars;
    }
    public function getProperties(){
        return $this->properties;
    }

    /**
     * @return Property
     */
    public function getProperty($name){
        if(array_key_exists($name, $this->properties)){
            return $this->properties[$name];
        }
    }
    /**
     * @return Variable
     */
    public function getVar($name){
        if(array_key_exists($name, $this->vars)){
            return $this->vars[$name];
        }
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

    private $return;
    private $doc         = "";
    private $vars        = [];
    private $throws      = [];
    private $properties  = [];
}
