<?php

namespace Entity;

use Entity\Node\InterfaceData;
use Entity\Node\ClassData;

class Index {
    private $fqcns              = [];
    private $classes            = [];
    private $classMap           = [];
    private $flippedClassMap    = [];
    private $extends            = [];
    private $implements         = [];
    private $parsedFiles        = [];

    public function getFQCNs(){
        return $this->fqcns;
    }
    public function addFQCN(FQCN $fqcn){
        $this->fqcns[$fqcn->toString()] = $fqcn;
    }
    public function findFQCNByFile($file){
        if(!array_key_exists($file, $this->flippedClassMap)){
            return null;
        }
        $fqcnStr = $this->flippedClassMap[$file];
        if(empty($fqcnStr)){
            return null;
        }
        if(!array_key_exists($fqcnStr, $this->fqcns)){
            return null;
        }
        return $this->fqcns[$fqcnStr];
    }

    public function getInterfaces(){
        return $this->interfaces;
    }
    public function addInterface(InterfaceData $interface){
        $this->interfaces[$interface->fqcn->toString()] = $interface;
    }

    public function getClasses(){
        return $this->classes;
    }
    public function findClassByFQCN(FQCN $fqcn){
        $str = $fqcn->toString();
        if(array_key_exists($str, $this->classes)){
            return $this->classes[$str];
        }
        return null;
    }
    public function addClass(ClassData $class, $key = null){
        if($key)
            $this->classes[$key] = $class;
        else
            $this->classes[$class->fqcn->toString()] = $class;
    }

    public function getClassMap(){
        return $this->classMap;
    }
    public function getFlippedClassMap(){
        return $this->classMap;
    }
    public function setClassMap(array $classMap){
        $this->classMap = $classMap;
        $this->flippedClassMap = array_flip($classMap);
    }
    public function getExtends(){
        return $this->extends;
    }
    public function addExtend($class, $parent){
        if(!array_key_exists($parent, $this->extends)
            || !is_array($this->extends[$parent])){
            $this->extends[$parent] = [];
        }
        if(!in_array($class, $this->extends[$parent])){
            $this->extends[$parent][] = $class;
        }
    }

    public function getImplements(){
        return $this->implements;
    }
    public function addImplement($class, $interface){
        if(!array_key_exists($interface, $this->implements)
            || !is_array($this->implements[$interface])){
            $this->implements[$interface] = [];
        }
        if(!in_array($class, $this->implements[$interface])){
            $this->implements[$interface][] = $class;
        }
    }

    public function isParsed($file){
        return array_key_exists(
            $file,
            $this->parsedFiles
        );
    }
    public function addParsedFile($file){
        $this->parsedFiles[$file] = $file;
    }
}
