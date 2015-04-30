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
    public function getEntries(Project $project, Context $context){
        $fqcn = $context->getData();
        $this->logger->addDebug('creating entries');
        if(!($fqcn instanceof FQCN)){
            return [];
        }
        $index = $project->getIndex();
        $this->logger->addDebug('Creating completion for ' . $fqcn->toString());
        $class = $index->findClassByFQCN($fqcn);
        $entries = [];
        if($class->methods !== null){
            foreach($class->methods->all() AS $method){
                $entry = $this->createEntryForMethod($method);
                $entries[] = $entry;
            }
        }
        if($class->properties !== null){
            foreach($class->properties->all() AS $property){
                $entries[] = $this->createEntryForProperty($property);
            }
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
