<?php

namespace DTO;

class Index {
    private $namespaces         = [];
    private $interfaces         = [];
    private $classes            = [];
    private $classMap           = [];
    private $flippedClassMap    = [];
    private $extends            = [];
    private $implements         = [];
    private $vendorLibs         = [];
    private $invalidClasses     = [];
    private $validClasses       = [];
    private $classesFQCN        = [];

    public function getNamespaces(){
        return $this->namespaces;
    }
    public function setNamespaces(array $namespaces){
        $this->namespaces = $namespaces;
    }
    public function addNamespace($namespace){
        $this->namespaces[] = $namespace;
    }

    public function getInterfaces(){
        return $this->interfaces;
    }
    public function setInterfaces(array $interfaces){
        $this->interfaces = $interfaces;
    }
    public function addInterface($interface){
        $this->interfaces[] = $interface;
    }

    public function getClasses(){
        return $this->classes;
    }
    public function setClasses(array $classes){
        $this->classes = $classes;
    }
    public function addClass($class, $key = null){
        if($key)
            $this->classes[$key] = $class;
        else 
            $this->classes[] = $class;
    }

    public function getClassMap(){
        return $this->classMap;
    }
    public function getFlippedClassMap(){
        return $this->flippedClassMap;
    }
    public function setClassMap(array $classMap){
        $this->classMap = $classMap;
        $this->flippedClassMap = array_flip($classMap);
    }
    public function setFlippedClassMap(array $flippedClassMap){
        $this->flippedClassMap = $flippedClassMap;
        $this->classMap = array_flip($flippedClassMap);
    }

    public function getExtends(){
        return $this->extends;
    }
    public function setExtends(array $extends){
        $this->extends = $extends;
    }
    public function addExtend($class, $parent){
        $this->extends[$parent][] = $class;
    }

    public function getImplements(){
        return $this->implements;
    }
    public function setImplements(array $implements){
        $this->implements = $implements;
    }
    public function addImplement($class, $interface){
        $this->implements[$interface][] = $class;
    }

    public function getVendorLibs(){
        return $this->vendorLibs;
    }
    public function setVendorLibs(array $vendorLibs){
        $this->vendorLibs = $vendorLibs;
    }

    public function getValidClasses(){
        return $this->validClasses;
    }
    public function getInvalidClasses(){
        return $this->invalidClasses;
    }
    public function addValidClass($class){
        $this->validClasses[] = $class;
    }
    public function addInvalidClass($class){
        $this->invalidClasses[] = $class;
    }

    public function getClassesFQCN(){
        return $this->classesFQCN;
    }
    public function setClassesFQCN(array $classesFQCN){
        $this->classesFQCN = $classesFQCN;
    }
    public function addClassFQCN(FQCN $fqcn){
        $this->classesFQCN[$fqcn->className] = $fqcn->toString();
    }
    public function getClassList(){
        return [];
    }
    public function toArray(){
        //@TODO fix format to get_object_vars
        return [
            "extends" => $this->getExtends(),
            "implements" => $this->getImplements(),
            "vendor_libs" => $this->getVendorLibs(),
            "namespaces" => $this->getNamespaces(),
            "interface" => $this->getInterfaces(),
            "fqcn_file" => $this->getClassMap(),
            "file_fqcn" => $this->getFlippedClassMap(),
            "classes" => $this->getClasses(),
            "class_fqcn" => $this->getClassesFQCN(),
            "class_list" => $this->getClassList(),
            "class_func_const_list" => [],
            "class_func_menu_entries" => [],
        ];
    }
}
