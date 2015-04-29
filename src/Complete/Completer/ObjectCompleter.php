<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;
use Entity\Completion\Entry;
use Entity\Completion\Scope;
use Psr\Log\LoggerInterface;

class ObjectCompleter {
    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }
    public function getEntries(Project $project, Context $context, Scope $scope){
        if($context->isThis()){
            return $this->getEntriesForThis($project, $context, $scope);
        }
        return $this->getEntriesForVar($project, $context, $scope);
    }
    protected function getEntriesForVar(
        Project $project,
        Context $context,
        Scope $scope
    ){
        $index = $project->getIndex();
        $varName = $context->getToken()->parent->symbol;
        if(empty($varName)){
            return [];
        }
        $varName = substr($varName, 1);
        $var = $scope->getVar($varName);
        if(empty($var)){
            return [];
        }
        $fqcn = $var->getFQCN();
        if(empty($fqcn)){
            return [];
        }
        $this->logger->addDebug('Creating completion for ' . $fqcn->toString());
        $class = $index->findClassByFQCN($fqcn);
        $entries = [];
        foreach($class->methods->all() AS $method){
            $entry = new Entry($method->name, $method->getSignature());
            $entries[] = $entry;
        }
        foreach($class->properties->all() AS $property){
            $entry = new Entry($property->name);
            $entries[] = $entry;
        }
        return $entries;
    }
    protected function getEntriesForThis(
        Project $project,
        Context $context,
        Scope $scope
    ){
        $index = $project->getIndex();
        $fqcn = $scope->getFQCN();
        if(empty($fqcn)){
            echo "Empty Oo\n";
            return [];
        }
        $class = $index->findClassByFQCN($scope->getFQCN());
        if(empty($class)){
            echo "Got empty class\n";
            return [];
        }
        $entries = [];
        foreach($class->methods->all() AS $method){
            $entry = new Entry($method->name, $method->getSignature());
            $entries[] = $entry;
        }
        foreach($class->properties->all() AS $property){
            $entry = new Entry($property->name);
            $entries[] = $entry;
        }
        return $entries;
    }

    private $logger;
}
