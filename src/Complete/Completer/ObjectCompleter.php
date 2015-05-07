<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\FQCN;
use Entity\Node\MethodData;
use Entity\Node\ClassProperty;
use Entity\Node\InterfaceData;
use Entity\Completion\Context;
use Entity\Completion\Entry;
use Entity\Completion\Scope;
use Entity\Collection\Specification;
use Psr\Log\LoggerInterface;

class ObjectCompleter {
    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }
    public function getEntries(Project $project, Context $context){
        /** @var FQCN $fqcn */
        list($fqcn, $isThis) = $context->getData();
        $this->logger->addDebug('creating entries');
        if(!$fqcn instanceof FQCN){
            return [];
        }
        $index = $project->getIndex();
        $this->logger->addDebug('Creating completion for ' . $fqcn->toString());
        $class = $index->findClassByFQCN($fqcn);
        if(empty($class)){
            $class = $index->findInterfaceByFQCN($fqcn);
        }
        if(empty($class)){
            return [];
        }
        $entries = [];
        $spec = new Specification($isThis ? 'private' : 'public');
        if($class->methods !== null){
            foreach($class->methods->all($spec) AS $method){
                $entry = $this->createEntryForMethod($method);
                $entries[$method->name] = $entry;
            }
        }
        if($class instanceof InterfaceData){
            return $entries;
        }
        if($class->properties !== null){
            foreach($class->properties->all($spec) AS $property){
                $entries[$property->name] = $this->createEntryForProperty($property);
            }
        }
        ksort($entries);
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
