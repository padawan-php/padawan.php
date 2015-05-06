<?php

namespace Entity;

use Entity\Node\InterfaceData;
use Entity\Node\ClassData;

class Index {
    private $fqcns              = [];
    private $classes            = [];
    private $interfaces         = [];
    private $classMap           = [];
    private $flippedClassMap    = [];
    private $extends            = [];
    private $implements         = [];
    private $parsedFiles        = [];

    public function getFQCNs(){
        return $this->fqcns;
    }

    /**
     * @return FQCN
     */
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

    /**
     * @return ClassData
     */
    public function findClassByFQCN(FQCN $fqcn){
        $str = $fqcn->toString();
        if(array_key_exists($str, $this->classes)){
            return $this->classes[$str];
        }
        return null;
    }

    /**
     * @return InterfaceData
     */
    public function findInterfaceByFQCN(FQCN $fqcn){
        $str = $fqcn->toString();
        if(array_key_exists($str, $this->interfaces)){
            return $this->interfaces[$str];
        }
    }
    public function findClassChildren(FQCN $class){
        if(!array_key_exists($class->toString(), $this->extends)
            || !is_array($this->extends[$class->toString()])
        ){
            $this->extends[$class->toString()] = [];
        }
        return $this->extends[$class->toString()];
    }
    public function getClasses(){
        return $this->classes;
    }
    public function addClass(ClassData $class){
        $this->classes[$class->fqcn->toString()] = $class;
        if(!empty($class->getParent())){
            $this->addExtend($class, $class->getParent());
        }
        foreach($this->findClassChildren($class->fqcn) AS $child){
            $child->setParent($class);
        }
    }
    public function addInterface(InterfaceData $interface){
        $this->interfaces[$interface->fqcn->toString()] = $interface;
    }
    public function getInterfaces(){
        return $this->interfaces;
    }
    public function addFQCN(FQCN $fqcn){
        $this->fqcns[$fqcn->toString()] = $fqcn;
    }

    public function getClassMap(){
        return $this->classMap;
    }
    public function getFlippedClassMap(){
        return $this->classMap;
    }
    public function getImplements(){
        return $this->implements;
    }
    public function getExtends(){
        return $this->extends;
    }

    public function setClassMap(array $classMap){
        $this->classMap = $classMap;
        $this->flippedClassMap = array_flip($classMap);
    }
    protected function addExtend(ClassData $class, FQCN $parent){
        $this->findClassChildren($parent);
        $this->extends[$parent->toString()][$class->fqcn->toString()] = $class;
        $parentClass = $this->findClassByFQCN($parent);
        if($parentClass instanceof ClassData){
            $class->setParent($parentClass);
        }
    }

    protected function addImplement($class, $interface){
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
