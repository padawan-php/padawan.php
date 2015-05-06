<?php

namespace Entity\Collection;

use Entity\Node\MethodData;
use Entity\Node\ClassData;
use Entity\Node\InterfaceData;

class MethodsCollection{
    private $methods = [];
    private $class;

    public function __construct($class){
        $this->class = $class;
    }
    public function add(MethodData $method){
        $this->methods[$method->name] = $method;
    }
    public function remove(MethodData $method){
        if(array_key_exists($method->name, $this->methods)){
            unset($this->methods[$method->name]);
        }
    }
    public function get($name, $mode = 'private', $magic = true){
        list($public, $protected, $private, $parentMode) = $this->expandMode($mode);
        if(array_key_exists($name, $this->methods)){
            return $this->methods[$name];
        }
        if($this->class instanceof InterfaceData){
            return;
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            return $parent->methods->get($name, $parentMode, $magic);
        }
    }
    public function all($mode='private', $magic=false){
        $methods = [];
        list($public, $protected, $private, $parentMode) = $this->expandMode($mode);
        foreach($this->methods AS $method){
            if(!$public && $method->isPublic()){
                continue;
            }
            if(!$protected && $method->isProtected()){
                continue;
            }
            if(!$private && $method->isPrivate()){
                continue;
            }
            if($magic !== $method->isMagic()){
                continue;
            }
            $methods[$method->name] = $method;
        }
        if($this->class instanceof ClassData){
            $parent = $this->class->getParent();
            if($parent instanceof ClassData){
                $methods = array_merge(
                    $parent->methods->all($parentMode, $magic),
                    $methods
                );
            }
        }
        sort($methods);
        return $methods;
    }
    protected function expandMode($mode){
        if($mode === 'private'){
            $public = $protected = $private = true;
            $parentMode = 'protected';
        }
        elseif($mode === 'protected'){
            $public = $protected = true;
            $private = false;
            $parentMode = 'protected';
        }
        else {
            $protected = $private = false;
            $public = true;
            $parentMode = 'public';
        }
        return [
            $public, $protected, $private, $parentMode
        ];
    }
}
