<?php

namespace Entity;

class FQCN {
    private $parts;
    public function __get($key){
        if($key === "className"){
            return $this->getClassName();
        }
        elseif($key === "namespace"){
            return $this->getNamespace();
        }
    }
    public function __construct($className, $namespace = ""){
        if($namespace){
            $this->parts = explode("\\", $namespace);
        }
        else {
            $this->parts = [];
        }
        $this->parts[] = $className;
    }
    public function join(FQCN $join){
        $result = new FQCN($this->getClassName(), $this->getNamespace());
        $resultParts = $result->getParts();
        $joiningParts = $join->getParts();
        if($result->getClassName() === $joiningParts[0]){
            array_shift($joiningParts);
        }
        $result->setParts(array_merge($resultParts, $joiningParts));
        return $result;
    }
    public function getClassName(){
        return $this->parts[count($this->parts) - 1];
    }
    public function getNamespace(){
        $parts = $this->parts;
        array_pop($parts);
        return implode("\\", $parts);
    }
    public function getParts(){
        return $this->parts;
    }
    public function setParts(array $parts){
        $this->parts = $parts;
    }
    public function toString(){
        return implode("\\", $this->parts);
    }
    public function __toString(){
        return $this->toString();
    }
}
