<?php

namespace Entity;

class FQN {

    public function __construct($namespace = ""){
        if($namespace){
            if($namespace instanceof FQN){
                $this->parts = $namespace->getParts();
            }
            elseif(!is_array($namespace)){
                $this->parts = explode("\\", $namespace);
            }
            else{
                $this->parts = $namespace;
            }
        }
        else {
            $this->parts = [];
        }
    }

    /**
     * Joins FQN to the clone of the current FQN and returns it
     *
     * @return FQN
     */
    public function join(FQN $join){
        $result = new self();
        $resultParts = $this->getParts();
        $joiningParts = $join->getParts();
        if($this->getLast() === $join->getFirst()){
            array_shift($joiningParts);
        }
        $result->setParts(array_merge($resultParts, $joiningParts));
        return $result;
    }
    public function getFirst(){
        $parts = $this->getParts();
        return array_shift($parts);
    }
    public function getLast(){
        $parts = $this->getParts();
        return array_pop($parts);
    }
    public function getTail(){
        $parts = $this->getParts();
        array_pop($parts);
        return $parts;
    }
    public function getParts(){
        if(is_array($this->parts)){
            return $this->parts;
        }
        return [];
    }
    public function setParts(array $parts){
        $this->parts = $parts;
    }
    public function addPart($part){
        $this->parts[] = $part;
    }
    public function toString(){
        return implode("\\", $this->getParts());
    }
    public function __toString(){
        return $this->toString();
    }

    public static $test;
    private $parts;
}
