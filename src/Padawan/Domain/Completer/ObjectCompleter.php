<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\FQCN;
use Padawan\Domain\Core\Node\MethodData;
use Padawan\Domain\Core\Node\ClassProperty;
use Padawan\Domain\Core\Node\InterfaceData;
use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Entry;
use Padawan\Domain\Core\Collection\Specification;
use Psr\Log\LoggerInterface;

class ObjectCompleter extends AbstractInCodeBodyCompleter
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function getEntries(Project $project, Context $context)
    {
        /** @var FQCN $fqcn */
        list($fqcn, $isThis) = $context->getData();
        $this->logger->debug('creating entries');
        if (!$fqcn instanceof FQCN) {
            return [];
        }
        $index = $project->getIndex();
        $this->logger->debug('Creating completion for ' . $fqcn->toString());
        $class = $index->findClassByFQCN($fqcn);
        if (empty($class)) {
            $class = $index->findInterfaceByFQCN($fqcn);
        }
        if (empty($class)) {
            return [];
        }
        $entries = [];
        $spec = new Specification($isThis ? 'private' : 'public');
        if ($class->methods !== null) {
            foreach ($class->methods->all($spec) as $method) {
                $entry = $this->createEntryForMethod($method);
                $entries[$method->name] = $entry;
            }
        }
        if ($class instanceof InterfaceData) {
            return $entries;
        }
        if ($class->properties !== null) {
            foreach ($class->properties->all($spec) as $property) {
                $entries[$property->name] = $this->createEntryForProperty($property);
            }
        }
        ksort($entries);
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        return parent::canHandle($project, $context) && ($context->isThis() || $context->isObject());
    }

    /**
     * Creates menu entry for MethodData
     *
     * @param MethodData $method a method
     * @return Entry
     */
    protected function createEntryForMethod(MethodData $method)
    {
        return new Entry(
            $method->name,
            $method->getSignature(),
            sprintf("%s\n%s\n", $method->getSignature(), $method->doc)
        );
    }

    protected function createEntryForProperty(ClassProperty $prop)
    {
        $type = $prop->type instanceof FQCN ? $prop->type->getClassName() : 'mixed';
        return new Entry(
            $prop->name,
            $type
        );
    }

    /** @property LoggerInterface */
    private $logger;
}
