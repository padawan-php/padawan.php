<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\FQCN;
use Entity\Node\MethodData;
use Entity\Node\ClassProperty;
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
            $entry = $this->createEntryForMethod($method);
            $entries[] = $entry;
        }
        foreach($class->properties->all() AS $property){
            $entries[] = $this->createEntryForProperty($property);
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
            $entry = $this->createEntryForMethod($method);
            $entries[] = $entry;
        }
        foreach($class->properties->all() AS $property){
            $entries[] = $this->createEntryForProperty($property);
        }
        return $entries;
    }
    /**
     * Creates menu entry for MethodData
     *
     * @param MethodData $method a method
     * @return Entry
     */
    protected function createEntryForMethod(MethodData $method){
        return new Entry(
            $method->name,
            $method->getSignature(),
            sprintf("%s\n%s\n", $method->getSignature(), $method->doc)
        );
    }

    protected function createEntryForProperty(ClassProperty $prop){
        $type = $prop->type instanceof FQCN ? $prop->type->toString() : 'mixed';
        return new Entry(
            $prop->name,
            $type
        );
    }

    /** @property LoggerInterface */
    private $logger;
}
