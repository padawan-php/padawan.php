<?php

namespace Entity\Collection;

use Entity\Node\MethodData;

class MethodsCollection{
    private $methods = [];
    public function add(MethodData $method){
        $this->methods[$method->name] = $method;
    }
    public function remove(MethodData $method){
        if(array_key_exists($method->name, $this->methods)){
            unset($this->methods[$method->name]);
        }
    }
    public function get($name){
        if(array_key_exists($name, $this->methods)){
            return $this->methods[$name];
        }
    }
    public function all(){
        return $this->methods;
    }
}
