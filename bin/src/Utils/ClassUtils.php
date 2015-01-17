<?php

namespace Utils;

use Validator\ClassValidator;
use Parser\ClassParser;

class ClassUtils{
    private $path;
    private $validator;
    private $parser;

    public function __construct(PathResolver $path, ClassParser $parser, ClassValidator $validator){
        $this->path = $path;
        $this->parser = $parser;
        $this->validator = $validator;
    }

    public function validate($file){
        return $this->validator->validate($file);
    }
    public function guessClass($classToken, $namespaces)
    {
        if(empty($classToken)) {
            return "";
        }
        if($classToken[0] == '\\') {
            return $classToken; //assuming it is a internal clas
        }
        //replace alias namespaces
        foreach ($namespaces['alias'] as $aliasName => $usesKey) {
            $useValue = $namespaces['uses'][$usesKey];
            if($usesKey == $useValue) {
                $namespaces['uses'][$aliasName] = $useValue;
            } else {
                $namespaces['uses'][$aliasName] = $useValue. "\\". $usesKey;
            }
        }

        $aliasReverse = array_flip($namespaces['alias']);
        if(array_key_exists($classToken, $namespaces['uses']) && !array_key_exists($classToken, $aliasReverse)){ //in uses
            if(array_key_exists($classToken, $namespaces['alias'])) {
                $fqcn = $namespaces['uses'][$classToken];
            }  else {
                $fqcn = $namespaces['uses'][$classToken]. "\\". $classToken;
            }
        } elseif(!empty($namespaces['alias']) && array_key_exists($classToken, $namespaces['alias'])) { //in alias
            $classToken = $namespaces['alias'][$classToken];
            $fqcn = $namespaces['uses'][$classToken]. "\\". $classToken;
        } else { //append with file namespace
            $fileNameSpace = "";
            if(array_key_exists('file', $namespaces)) { //has namespace
                $fileNameSpace = $namespaces['file'] . "\\";

            }
            $fqcn =  trim($fileNameSpace. $classToken);
        }

        if($fqcn == "" && array_key_exists($classToken, $this->fqcn_file)){
            $fqcn = $classToken;
        }

        if(array_key_exists($fqcn, $this->fqcn_file)) {
            return $fqcn;
        } else{
            try {
                $reflectionClass = new \ReflectionClass($fqcn);
                return $fqcn;
            } catch (\Exception $e) {
                //ldd($e->getMessage());
                $fqcn = "";
            }
            if(empty($fqcn)){
                try {
                    $reflectionClass = new \ReflectionClass($classToken);
                    return $classToken;
                } catch (\Exception $e) {
                    $fqcn = "";
                }
            }
        }
        return $fqcn;
    }
    public function processClass($fqcn)
    {
        if(array_key_exists($fqcn, $this->processedClasses)) {
            return $this->processedClasses[$fqcn];
        }
        $out = array();
        $classData = $this->getClassInfo($fqcn);

        $baseProperties = $classData;
        $parentClass = $classData['parentclass'];
        $parentProperties = array();
        if(!empty($parentClass)) {
            $parentProperties = $this->processClass($parentClass);
        }
        foreach($classData['interfaces'] as $interface) {
            $interfaceProperties = $this->processClass($interface);
            $baseProperties = $this->mergeClassProperties($baseProperties, $interfaceProperties);
        }
        $merge = $this->mergeClassProperties($baseProperties, $parentProperties);

        $this->processedClasses[$fqcn] = $merge;

        return $merge;
    }

    private function mergeClassProperties($baseProperties, $parentProperties)
    {
        if(empty($parentProperties)) {
            return $baseProperties;
        }
        if(empty($baseProperties)) {
            return $parentProperties;
        }

        $mergedProperties = $this->getEmptyMergeProperty();

        foreach($baseProperties['methods']['modifier'] as $modifier => $methods) {
            $mergedProperties['methods']['modifier'][$modifier] = array_unique(array_merge($baseProperties['methods']['modifier'][$modifier], $parentProperties['methods']['modifier'][$modifier]));
        }
        $basePropertiesMethods = array_keys($baseProperties['methods']['all']);
        $parentPropertiesMethods = array_keys($parentProperties['methods']['all']);
        $combinedMethods = array_merge($basePropertiesMethods, $parentPropertiesMethods);
        $methodSummary = array_count_values($combinedMethods);
        foreach($methodSummary as $method => $count) {
            if($count == 1) {
                if(in_array($method, $basePropertiesMethods)) {
                    $mergedProperties['methods']['all'][$method] = $baseProperties['methods']['all'][$method];
                }
                if(in_array($method, $parentPropertiesMethods)) {
                    $mergedProperties['methods']['all'][$method] = $parentProperties['methods']['all'][$method];
                }
            } else if($count == 2) {
                $basePropertiesMethod = $baseProperties['methods']['all'][$method];
                $parentPropertiesMethod = $parentProperties['methods']['all'][$method];
                $mergedProperties['methods']['all'][$method] = $parentPropertiesMethod;
                if(is_array($parentPropertiesMethod) && !$parentPropertiesMethod['inheritdoc']) {
                    $mergedProperties['methods']['all'][$method] = $parentPropertiesMethod;
                }
                if(is_array($parentPropertiesMethod) && !$basePropertiesMethod['inheritdoc']) {
                    $mergedProperties['methods']['all'][$method] = $basePropertiesMethod;
                }
            }
        }
        if(!array_key_exists('alias', $parentProperties['namespaces'])) {
            $parentProperties['namespaces']['alias'] = array();
        }
        if(!array_key_exists('alias', $baseProperties['namespaces'])) {
            $parentProperties['namespaces']['alias'] = array();
        }
        $mergedProperties['namespaces']['uses'] = array_merge( $parentProperties['namespaces']['uses'] , $baseProperties['namespaces']['uses']);
        $mergedProperties['namespaces']['alias'] = array_merge( $parentProperties['namespaces']['alias'] , $baseProperties['namespaces']['alias']);

        if(array_key_exists('file', $baseProperties['namespaces'])) {
            $mergedProperties['namespaces']['file'] = $baseProperties['namespaces']['file'];
        }

        foreach($baseProperties['properties']['modifier'] as $modifier => $properties) {
            $mergedProperties['properties']['modifier'][$modifier] = array_unique(array_merge($baseProperties['properties']['modifier'][$modifier], $parentProperties['properties']['modifier'][$modifier]));
        }
        $mergedProperties['properties']['all'] = array_merge($baseProperties['properties']['all'], $parentProperties['properties']['all']);
        $mergedProperties['parentclass'] = isset($baseProperties['parentclass'])? $baseProperties['parentclass']: "";

        $mergedProperties['parentclasses'] = array();
        array_push($mergedProperties['parentclasses'], $baseProperties['parentclass'], $parentProperties['parentclass']);
        $mergedProperties['parentclasses'] = array_unique($mergedProperties['parentclasses']);
        $mergedProperties['interfaces'] = array_unique(array_merge($baseProperties['interfaces'], $parentProperties['interfaces']));

        $mergedProperties['parentclasses'] = array_filter($mergedProperties['parentclasses'], function($var){
            return !empty($var);
        });

        $mergedProperties['interfaces'] = array_filter($mergedProperties['interfaces'], function($var){
            return !empty($var);
        });

        $mergedProperties['constants'] = array_unique(array_merge($baseProperties['constants'], $parentProperties['constants']));

        $mergedProperties['file']       = $baseProperties['file'];
        $mergedProperties['startLine']  = $baseProperties['startLine'];
        $mergedProperties['docComment'] = $baseProperties['docComment'];
        $mergedProperties['classname']  = $baseProperties['classname'];
        return $mergedProperties;
    }


    private function getClassInfo($fqcn)
    {
        $classData = $this->getEmptyClassData($fqcn);
        if(empty($fqcn)) {
            return $classData;
        }
        try {
            $reflectionClass = new \ReflectionClass($fqcn);
        } catch (\Exception $e) {
            return $classData;
        }

        if($reflectionClass->isInternal()) {
            $fqcn = $reflectionClass->getName();
            if($this->coreIndex['classes'] && array_key_exists($fqcn, $this->coreIndex['classes'])) {
                return $this->coreIndex['classes'][$reflectionClass->getName()];
            } else {
                return $classData;
            }
        }
        $classContent = array();

        $classData['file']      = $reflectionClass->getFileName();
        $classData['startLine'] = $reflectionClass->getStartLine();
        $docComment             = $reflectionClass->getDocComment();
        if(!$docComment) {
            $docComment = "";
        }
        preg_match("/(\\\\)?(\w+)$/", $reflectionClass->name, $classNameMatches);
        $classData['docComment']         = $this->trimDocComment($docComment);
        $classContent                    = $this->getClassContent($reflectionClass->getFileName(), $fqcn);

        $classData['namespaces']['file'] = $reflectionClass->getNamespaceName();
        $parsedClassData                 = $this->parseClass($classData['file']);
        $className                       = $classNameMatches[2];
        $classData['namespaces']         = $parsedClassData['namespaces'];
        $classData['className']          = $className;
        $this->classes[]                 = $className;

        if(array_key_exists($className, $this->class_fqcn)) {
            if(is_array($this->class_fqcn[$className])
                && !in_array($fqcn, $this->class_fqcn[$className])
            ) {

                $this->class_fqcn[$className][] = $fqcn;

            } elseif(is_string($this->class_fqcn[$className])
                && $this->class_fqcn[$className] != $fqcn
            ) {

                $fqcns                        = array();
                $fqcns[]                      = $this->class_fqcn[$className];
                $fqcns[]                      = $fqcn;
                $this->class_fqcn[$className] = $fqcns;
            }
        } else {
            $this->class_fqcn[$className] = $fqcn;
        }

        $this->getConstantData($reflectionClass, $classData);

        $classMethods = $reflectionClass->getMethods();

        foreach($classMethods as $reflectionMethod) {
            if($reflectionMethod->class === $fqcn) {
                $this->getMethodData($reflectionMethod, $classContent, $classData);
            }
        }

        $classProperties = $reflectionClass->getProperties();
        foreach($classProperties as $classProperty) {
            if($classProperty->class == $fqcn){
                $this->getPropertyData($classProperty, $classData);
            }
        }

        $classData['parentclass'] = "";
        $parentClass = $reflectionClass->getParentClass();
        //if($parentClass && !$parentClass->isInternal() ){
        if($parentClass){
            $classData['parentclass'] = $parentClass->getName();
        }
        $classData['interfaces'] = $reflectionClass->getInterfaceNames();
        $classData['classname'] = $className;

        return $classData;
    }

    private function getMethodData(ReflectionMethod $reflectionMethod, $classContent, &$classData)
    {
        //TODO: parse local variable
        $modifiers  = Reflection::getModifierNames($reflectionMethod->getModifiers());
        $startLine  = $reflectionMethod->getStartLine();
        $endLine    = $reflectionMethod->getEndline();
        $parameters = $reflectionMethod->getParameters();
        $docComment = $reflectionMethod->getDocComment();
        if(!$docComment) {
            $docComment = "";
        }
        $parsedComment     = $this->parseDocComment($docComment, 'method');
        $out               = array();
        $out['params']     = array();
        $out['docComment'] = $this->trimDocComment($docComment);
        $out['inheritdoc'] = $parsedComment['inheritdoc'];
        $out['startLine']  = $startLine;
        $out['endLine']    = $endLine;

        $origin = $reflectionMethod->getDeclaringClass()->getFileName() == false? "": $reflectionMethod->getDeclaringClass()->getFileName();
        $origin = $this->normalizePath($origin);
        $out['origin'] = $origin;

        $params = array();
        foreach ($parameters as $parameter) {
            $parameterName = '$'.$parameter->getName();
            try {
                $parameterClass = $parameter->getClass();
            } catch (\Exception $e) {
                $parameterClass = "";
            }
            $parameterType = "";
            //not defined try to find in the doccomment
            if(empty($parameterClass)) {
                if(array_key_exists($parameterName, $parsedComment['params'])) {
                    $parameterType = $parsedComment['params'][$parameterName];
                }
            } else {
                $parameterType = $parameter->getClass()->getName();
            }
            $params[] = $parameterType. ' '. $parameterName;
            $out['params'][$parameterName] = $parameterType;
        }

        if(array_key_exists('return', $parsedComment) && $parsedComment['return'] != "") {
            $returnType  = "";
            $arrayReturn = 0;
            $returnTypes = explode('|', trim($parsedComment['return']));
            foreach ($returnTypes as $rType) {
                if(preg_match('/\[\]$/', $rType)) {
                    $arrayReturn = 1;
                    $rType = trim($rType, "[]");
                }
                if(!$this->isScalar($rType)) {
                    $returnType = $rType;
                    if($returnType[0] == '\\') {
                        $returnType = substr($returnType, 1);
                    }
                    break;
                }
            }
            if(empty($returnType)) {
                $out['return'] = "";
            } else {
                $out['return'] = $this->guessClass($returnType, $classData['namespaces']);
            }
            $out['array_return'] = $arrayReturn;
        }

        $return = empty($parsedComment['return'])? "none" : $parsedComment['return'];
        $out['signature']  = '('. join(', ', $params) . ')  : '. $return;

        foreach($modifiers as $modifier) {
            $classData['methods']['modifier'][$modifier][] = $reflectionMethod->name;
        }

        $classData['methods']['all'][$reflectionMethod->name] = $out;
    }

    private function getConstantData(ReflectionClass $reflectionClass, &$classData)
    {
        $constants = array();
        try {
            $constants = $reflectionClass->getConstants();
        } catch (\Exception $e) {
            //echo  $e->getMessage();
        }
        foreach ($constants as $key => $value) {
            if(is_string($value)) {
                $constants[$key] = $this->fixUTF8($value);
            }
        }
        $classData['constants'] = $constants;
    }

    private function parseDocComment($docComment, $type)
    {
        $out = array();
        $comments = explode('*', $docComment);
        if(count($comments) > 1) {
            //var_dump($comments);
        }
        if($type == 'method') {
            $out['params'] = array();
            $out['return'] = "";
            $out['inheritdoc'] = 0;
        } else if($type == 'property') {
            $out['var'] = "";
            $out['inheritdoc'] = 0;
        }
        foreach ($comments as $comment) {
            $comment = trim(trim($comment), "{}/");
            $splits = preg_split('/\s+/', $comment);
            if(empty($splits)) {
                continue;
            }
            if($type=='method'){
                switch ($splits[0]) {
                case '@param':
                    if(count($splits) == 1) {
                        continue;
                    } elseif(count($splits) == 2) {
                        $splits[2] = $splits[1];
                    }
                    $out['params'][$splits[2]] = $splits[1];
                    break;
                case '@return':
                    if(count($splits) < 2){
                        continue;
                    }
                    $out['return'] = $splits[1];
                    break;
                case '@inheritdoc':
                    $out['inheritdoc'] = 1;
                    break;
                }
            } else if($type == 'property') {
                if($splits[0] == '@var') {
                    if(count($splits) < 2) {
                        continue;
                    }
                    $out['var'] = $splits[1];
                }
            }
        }
        return $out;
    }

    private function getPropertyData(ReflectionProperty $reflectionProperty, &$classData)
    {
        $sclarTypes = array('boolean', 'integer','float', 'string', 'array', 'object', 'resource', 'mixed', 'number', 'callback');
        $modifiers = Reflection::getModifierNames($reflectionProperty->getModifiers());
        $docComment = $reflectionProperty->getDocComment();
        if(!$docComment) {
            $docComment = "";
        }
        $parsedComment = $this->parseDocComment($docComment, 'property');
        $type = $parsedComment['var'];
        $out = array();
        $out['type'] = "";
        $out['array_type'] = 0;
        if(preg_match('/\[\]$/', $type)) {
            $out['array_type'] = 1;
        }
        if(!$this->isScalar($type) && !empty($type)) {
            if($type[0] == '\\') {
                $type = substr($type, 1);
            }
            if(preg_match('/\[\]$/', $type)) {
                $type = trim($type, "[]");
            }
            $out['type'] = $this->guessClass($type, $classData['namespaces']);
        }
        $out['inheritdoc'] = $parsedComment['inheritdoc'];
        $out['docComment'] = $this->trimDocComment($docComment);
        $origin = $reflectionProperty->getDeclaringClass()->getFileName() == false? "": $reflectionProperty->getDeclaringClass()->getFileName();
        $origin = $this->normalizePath($origin);
        $out['origin'] = $origin;
        $classData['properties']['all'][$reflectionProperty->name] = $out;
        foreach ($modifiers as $modifier) {
            $classData['properties']['modifier'][$modifier][] = $reflectionProperty->name;
        }
    }

    private function trimDocComment($docComment)
    {
        $comments = explode("\n", $docComment);
        $newComments = array();
        $count = 1;
        foreach ($comments as $comment) {
            $comment = trim($comment);
            $comment = str_replace("/**", '', $comment, $count);
            $comment = str_replace("*", '', $comment, $count);
            $comment = str_replace("*/", '', $comment, $count);
            $comment = str_replace("/", '', $comment, $count);
            $newComments[] = $comment;
        }
        $docComment = join("\n", $newComments);
        $docComment = $this->fixUTF8($docComment);
        return $docComment;
    }

    private function fixUTF8($content)
    {
        return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
    }


    private function getEmptyMergeProperty()
    {
        return array(
            'methods' => array(
                'modifier' => array(
                    'public' => array(),
                    'private' => array(),
                    'protected' => array(),
                    'final' => array(),
                    'static' => array(),
                    'interface' => array(),
                    'abstract' => array(),
                ),
                //'all' => array(),
                'all' => array("nnnnnnnn" => "nnnnnnnnnnnn")
            ),
            'namespaces' => array(
                'uses' => array(),
                'alias' => array()
            ),
            'properties' => array(
                'modifier' => array(
                    'public' => array(),
                    'private' => array(),
                    'protected' => array(),
                    'final' => array(),
                    'static' => array(),
                    'interface' => array(),
                    'abstract' => array(),
                ),
                //'all' => array()
                'all' => array("nnnnnnnn" => "nnnnnnnnnnnn"),
                "origin" => "",
            ),
            'constants' => array(),
            'parentclasses' => array(),
            'interfaces' => array(),
            'classname' => "",
            'startLine' => 0,
            'docComment' => "",
            'file' => ''
        );
    }

    private function getEmptyClassData($className)
    {
        $classData = array();
        $classData['classname'] = $className;
        $classData['startLine'] = 1;
        $classData['docComment'] = "";
        $classData['methods'] = array(
            'modifier' => array(
                'public' => array(),
                'private' => array(),
                'protected' => array(),
                'final' => array(),
                'static' => array(),
                'interface' => array(),
                'abstract' => array(),
            ),
            'all' => array("nnnnnnnn" => "nnnnnnnnnnnn"),
            'origin' => ""
        );
        $classData['properties'] = array(
            'modifier' => array(
                'public' => array(),
                'private' => array(),
                'protected' => array(),
                'final' => array(),
                'static' => array(),
                'interface' => array(),
                'abstract' => array(),
            ),
            'all' => array("nnnnnnnn" => "nnnnnnnnnnnn")
        );
        $classData['parentclass'] = "";
        $classData['parentclasses'] = array();
        $classData['interfaces'] = array();
        $classData['file'] = "";
        $classData['namespaces'] = array(
            'uses' => array(),
            'alias' => array(),
        );

        $classData['constants'] = array();

        return $classData;
    }

    private function isScalar($type)
    {
        $scalarsTypes = array('boolean', 'integer','float', 'string', 'array', 'object',
            'resource', 'mixed', 'number', 'callback', 'null', 'void', 'bool', 'self', 'int', 'callable');
        return in_array(strtolower($type), $scalarsTypes);
    }

    private function getFQCNfromFile($file, $file_fqcn)
    {
        if(array_key_exists($file, $this->file_fqcn)) {
            return $this->file_fqcn[$file];
        } else{
            $parsedData = parseClass($file);
            $classLineData = $parsedData['class_line_data'];
            $className = $classLineData['classname'];
            if(array_key_exists('file', $parsedData['namespaces'])) {
                $fqcn = $parsedData['namespaces']['file'] . "\\" . $className;
            } else {
                $fqcn = $className;
            }
            return $fqcn;
        }
    }


}
