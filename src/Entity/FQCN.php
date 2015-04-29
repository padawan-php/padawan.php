<?php

namespace Entity;

class FQCN {

    public function __get($key){
        if($key === "className"){
            return $this->getClassName();
        }
        elseif($key === "namespace"){
            return $this->getNamespace();
        }
    }
    public function __construct($className, $namespace = "", $isArray=false){
        if($namespace){
            if(!is_array($namespace)){
                $this->parts = explode("\\", $namespace);
            }
            else{
                $this->parts = $namespace;
            }
        }
        else {
            $this->parts = [];
        }
        $this->_isArray = $isArray;
        $this->_isScalar = false;
        if(count($this->parts) === 0){
            switch($className){
            case "int":
            case "string":
            case "float":
            case "array":
                $this->_isScalar = true;
                break;
            }
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
    public function isArray(){
        return $this->_isArray;
    }
    public function isScalar(){
        return $this->_isScalar;
    }

    private $parts;
    private $_isArray;
    private $_isScalar;
}
