<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Project;

class CompleterFactory {
    public function __construct(
        ClassNameCompleter $classNameCompleter,
        InterfaceNameCompleter $interfaceNameCompleter,
        NamespaceCompleter $namespaceCompleter,
        ObjectCompleter $objectCompleter,
        StaticCompleter $staticCompleter,
        UseCompleter $useCompleter
    ){
        $this->classNameCompleter = $classNameCompleter;
        $this->interfaceNameCompleter = $interfaceNameCompleter;
        $this->namespaceCompleter = $namespaceCompleter;
        $this->objectCompleter = $objectCompleter;
        $this->staticCompleter = $staticCompleter;
        $this->useCompleter = $useCompleter;
    }
    public function getCompleter(Context $context){
        if($context->isNamespace()){
            return $this->namespaceCompleter;
        }
        elseif($context->isUse()){
            return $this->useCompleter;
        }
        elseif($context->isClassName()){
            return $this->classNameCompleter;
        }
        elseif($context->isInterfaceName()){
            return $this->interfaceNameCompleter;
        }
        elseif($context->isThis() || $context->isObject()){
            return $this->objectCompleter;
        }
        elseif($context->isClassStatic()){
            return $this->staticCompleter;
        }
        return null;
    }

    private $classNameCompleter;
    private $interfaceNameCompleter;
    private $namespaceCompleter;
    private $objectCompleter;
    private $staticCompleter;
    private $useCompleter;
}
