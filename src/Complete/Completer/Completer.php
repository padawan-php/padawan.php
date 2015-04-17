<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Entity\Project;

class Completer{
    private $classNameCompleter;
    private $interfaceNameCompleter;
    private $namespaceCompleter;

    public function __construct(
        ClassNameCompleter $classNameCompleter,
        InterfaceNameCompleter $interfaceNameCompleter,
        NamespaceCompleter $namespaceCompleter
    ){
        $this->classNameCompleter = $classNameCompleter;
        $this->interfaceNameCompleter = $interfaceNameCompleter;
        $this->namespaceCompleter = $namespaceCompleter;
    }
    public function getEntries(Project $project, Context $context){
        if($context->isNamespace()){
            printf("Namespace completion");
            return $this->getAllNamespaces($project, $context);
        }
        elseif($context->isClassName()){
            printf("Classname completion");
            return $this->getAllClasses($project, $context);
        }
        elseif($context->isInterfaceName()){
            printf("Interfaces completion");
            return $this->getAllInterfaces($project, $context);
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
}
