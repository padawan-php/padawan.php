<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Node\MethodData;
use Padawan\Domain\Project\Node\ClassProperty;
use Padawan\Domain\Project\Node\InterfaceData;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;
use Padawan\Domain\Project\Collection\Specification;
use Psr\Log\LoggerInterface;

class StaticCompleter extends AbstractInCodeBodyCompleter
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getEntries(Project $project, Context $context)
    {
        /** @var FQCN $fqcn */
        /** @var \PhpParser\Node\Name $workingNode */
        list($fqcn, $isThis, $_, $workingNode) = $context->getData();
        $workingNode = $workingNode->getLast();
        $isThis = $workingNode == 'self' || $workingNode == 'static';

        $this->logger->debug('creating static entries for type ' . $fqcn->toString());

        if (!$fqcn instanceof FQCN) {
            return [];
        }
        $index = $project->getIndex();
        $class = $index->findClassByFQCN($fqcn);
        if (empty($class)) {
            $class = $index->findInterfaceByFQCN($fqcn);
        }
        if (empty($class)) {
            return [];
        }
        $entries = [];
        $spec = new Specification($isThis ? 'private' : 'public', 1);
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
                $entries['$' . $property->name] = $this->createEntryForProperty($property);
            }
        }
        if ($class->constants !== null) {
            foreach ($class->constants->all() as $const) {
                $entries[$const] = $this->createEntryForConst($const);
            }
        }
        ksort($entries, SORT_NATURAL);
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        return parent::canHandle($project, $context) && $context->isClassStatic();
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
        $type = $prop->type instanceof FQCN ? $prop->type->toString() : 'mixed';
        return new Entry(
            '$' . $prop->name,
            $type
        );
    }

    protected function createEntryForConst($const)
    {
        return new Entry($const);
    }

    /** @property LoggerInterface */
    private $logger;
}
