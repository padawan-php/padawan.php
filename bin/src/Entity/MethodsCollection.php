<?php

namespace Entity;

class MethodsCollection{
    private $methods = [];
    public function add(MethodData $method){
        foreach($this->methods AS $curMethod){
            if($curMethod->name === $method->name){
                return;
            }
        }
        $this->methods[] = $method;
    }
    public function remove(MethodData $method){
        if(($key = array_search($method, $this->methods)) !== false){
            unset($this->methods[$key]);
        }

    }
    public function toArray(){
        $map = [
            "modifier" => [
                "public" => [],
                "private" => [],
                "protected" => [],
                "abstract" => [],
                "final" => [],
                "static" => []
            ],
            "all" => [

            ]
        ];
        foreach($this->methods AS $method){
            if($method->isPublic()){
                $map["modifier"]["public"][] = $method->name;
            }
            if($method->isProtected()){
                $map["modifier"]["protected"][] = $method->name;
            }
            if($method->isPrivate()){
                $map["modifier"]["private"][] = $method->name;
            }
            if($method->isStatic()){
                $map["modifier"]["public"][] = $method->name;
            }
            if($method->isFinal()){
                $map["modifier"]["final"][] = $method->name;
            }
            if($method->isAbstract()){
                $map["modifier"]["abstract"][] = $method->name;
            }
            $map["all"][$method->name] = $method->toArray();
        }
        return $map;
    }
}
