<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Project;

class Completer{
    public function __construct(
        ClassNameCompleter $classNameCompleter,
        InterfaceNameCompleter $interfaceNameCompleter,
        NamespaceCompleter $namespaceCompleter,
        ObjectCompleter $objectCompleter
    ){
        $this->classNameCompleter = $classNameCompleter;
        $this->interfaceNameCompleter = $interfaceNameCompleter;
        $this->namespaceCompleter = $namespaceCompleter;
        $this->objectCompleter = $objectCompleter;
    }
    public function getEntries(Project $project, Context $context){
        if($context->isNamespace()){
            return $this->getAllNamespaces($project, $context);
        }
        elseif($context->isClassName()){
            return $this->getAllClasses($project, $context);
        }
        elseif($context->isInterfaceName()){
            return $this->getAllInterfaces($project, $context);
        }
        elseif($context->isThis() || $context->isObject()){
            return $this->getObjectCompletion($project, $context);
        }
        return [];
    }
    protected function getAllNamespaces(Project $project, Context $context){
        return $this->namespaceCompleter->getEntries($project, $context);
    }
    protected function getAllClasses(Project $project, Context $context){
        return $this->classNameCompleter->getEntries($project, $context);
    }
    protected function getAllInterfaces(Project $project, Context $context){
        return $this->interfaceNameCompleter->getEntries($project, $context);
    }
    protected function getObjectCompletion(Project $project, Context $context){
        return $this->objectCompleter->getEntries($project, $context);
    }

    private $classNameCompleter;
    private $interfaceNameCompleter;
    private $namespaceCompleter;
    private $objectCompleter;
}
