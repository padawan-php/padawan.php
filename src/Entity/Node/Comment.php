<?php

namespace Entity\Node;

use Entity\FQCN;
use Entity\Node\ClassProperty;
use Entity\Node\Variable;

class Comment {
    const INHERIT_MARK = 'inheritdoc';

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
     * @return ClassProperty
     */
    public function getProperty($name){
        $prop = null;
        if(array_key_exists($name, $this->properties)){
            $prop = $this->properties[$name];
        }
        if(!$prop instanceof ClassProperty && array_key_exists('', $this->properties)){
            $prop = $this->properties[''];
        }
        if(empty($prop)){
            $var = $this->getVar($name);
            if($var instanceof Variable){
                $prop = new ClassProperty;
                $prop->name = $var->getName();
                $prop->setType($var->getType());
            }
        }
        return $prop;
    }

    /**
     * @return Variable
     */
    public function getVar($name){
        $var = null;
        if(array_key_exists($name, $this->vars)){
            $var = $this->vars[$name];
        }
        if(!$var instanceof Variable && array_key_exists('', $this->vars)){
            $var = $this->vars[''];
        }
        return $var;
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
    public function markInheritDoc(){
        $this->inheritDoc = true;
    }
    public function isInheritDoc(){
        return $this->inheritDoc;
    }

    private $return;
    private $doc         = "";
    private $vars        = [];
    private $throws      = [];
    private $properties  = [];
    private $inheritDoc  = false;
}
