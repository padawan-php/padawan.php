<?php

namespace Entity;

class FQCN extends FQN {

    public function __get($key){
        if($key === "className"){
            return $this->getClassName();
        }
        elseif($key === "namespace"){
            return $this->getNamespace();
        }
    }
    public function __construct($className, $namespace = "", $isArray=false){
        parent::__construct($namespace);
        $this->_isArray = $isArray;
        $this->_isScalar = false;
        if(count($this->parts) === 0){
            switch($className){
            case "int":
            case "string":
            case "float":
            case "array":
            case "mixed":
            case "void":
            case "object":
            case "bool":
            case "null":
            case "false":
            case "true":
                $this->_isScalar = true;
                break;
            }
        }
        $this->addPart($className);
    }

    /**
     * @inheritdoc
     */
    public function join(FQN $join){
        $result = new self($join->getLast());
        $resultParts = $this->getParts();
        $joiningParts = $join->getParts();
        if($this->getLast() === $join->getFirst()){
            array_shift($joiningParts);
        }
        $result->setParts(array_merge($resultParts, $joiningParts));
        return $result;
    }
    public function getClassName(){
        return $this->getLast();
    }
    public function getNamespace(){
        $parts = $this->getParts();
        array_pop($parts);
        return implode("\\", $parts);
    }
    public function toString(){
        $str = parent::toString();
        if($this->isArray()){
            $str .= '[]';
        }
        return $str;
    }
    public function isArray(){
        return $this->_isArray;
    }
    public function isScalar(){
        return $this->_isScalar;
    }

    private $_isArray;
    private $_isScalar;
}
