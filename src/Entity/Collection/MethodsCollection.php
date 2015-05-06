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
    public function get($name, Specifiaction $spec = null){
        if($spec === null){
            $spec = new Specification;
        }
        if(array_key_exists($name, $this->methods)){
            $method = $this->methods[$name];
            if($spec->satisfy($method)){
                return $method;
            }
            return null;
        }
        if($this->class instanceof InterfaceData){
            return;
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            return $parent->methods->get($name, new Specification(
                $spec->getParentMode(),
                $spec->isStatic(),
                $spec->isMagic()
            ));
        }
    }
    public function all(Specification $spec = null){
        if($spec === null){
            $spec = new Specification;
        }
        $methods = [];
        foreach($this->methods AS $method){
            if($spec->satisfy($method)){
                $methods[$method->name] = $method;
            }
        }
        if($this->class instanceof ClassData){
            $parent = $this->class->getParent();
            if($parent instanceof ClassData){
                $methods = array_merge(
                    $parent->methods->all(new Specification(
                        $spec->getParentMode(),
                        $spec->isStatic(),
                        $spec->isMagic()
                    )),
                    $methods
                );
            }
        }
        sort($methods);
        return $methods;
    }
}
