<?php

namespace Validator;

use Parser\ClassParser;
use Utils\ClassUtils;

class ClassValidator{
    private $validatingFiles;
    private $validFiles;
    private $parser;
    private $utils;

    public function __construct(ClassParser $parser = null, ClassUtils $utils = null){
        $this->validatingFiles  = [];
        $this->validFiles       = [];
        $this->parser           = $parser;
        $this->utils            = $utils;
    }
    public function setParser(ClassParser $parser){
        $this->parser = $parser;
    }
    public function setUtils(ClassUtils $utils){
        $this->utils = $utils;
    }
    public function validate($fileName){
        $this->validatingFiles = [];
        return $this->validateClass($fileName);
    }
    protected function addToValidating($fileName){
        $this->validatingFiles[] = $fileName;
    }
    public function validateClass($fileName)
    {
        $this->addToValidating($fileName);

        if(!is_file($fileName)) {
            return false;
        }

        if(array_key_exists($fileName, $this->validFiles)) {
            return $this->validFiles[$fileName];
        }

        $parsedClassData = $this->parser->parseClass($fileName);
        $namespaces = $parsedClassData['namespaces'];
        $classLineData = $parsedClassData['class_line_data'];
        $classTokens = array();
        $classTokens[] = $classLineData['extends'];
        $classTokens = array_merge($classTokens,  $classLineData['implements']);
        $className = $classLineData['classname'];
        $tokens = array();
        //print_r($namespaces);exit;
        foreach($classTokens as $classToken) {
            if(empty($classToken)) {
                continue;
            }
            $fqcn = $this->guessClass($classToken, $namespaces);
            if(empty($fqcn)) {
                return false;
            }
            if(!array_key_exists($fqcn, $this->fqcn_file)) { //it may be an internal interface
                try {
                    $reflectionClass = new \ReflectionClass($fqcn);
                    continue;
                } catch (\Exception $e) {
                    return false;
                }
            }
            $isValidFqcnProperties = $this->validateClass($this->fqcn_file[$fqcn], $i+1);
            if(!$isValidFqcnProperties) {
                return false;
            }
        }
        foreach($parsedClassData['constructor_arguments'] as $classToken) {
            if(empty($classToken)) {
                continue;
            }
            if($classToken == $className) {
                continue;
            }
            $fqcn = $this->guessClass($classToken, $namespaces);
            if(empty($fqcn)) {
                return false;
            }
            if(!array_key_exists($fqcn, $this->fqcn_file)) { //it may be an internal interface
                try {
                    $reflectionClass = new \ReflectionClass($fqcn);
                    continue;
                } catch (\Exception $e) {
                    return false;
                }
            }
            $isValidFqcnProperties = $this->validateClass($this->fqcn_file[$fqcn], $i+1);
            if(!$isValidFqcnProperties) {
                return false;
            }
        }

        if(array_key_exists($className, $this->fqcn_file) && $this->fqcn_file[$className] == $fileName) {
            $classFqcn = $className;
        } else{
            $classFqcn = $this->guessClass($className, $namespaces);
        }

        if(empty($classFqcn)) {
            return false;
        }

        $this->validFiles[$fileName] = $classFqcn;
        return $classFqcn;
    }
}
