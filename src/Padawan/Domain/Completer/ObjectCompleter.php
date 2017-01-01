<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\ClassRepository;
use Padawan\Domain\Project\Node\MethodData;
use Padawan\Domain\Project\Node\ClassProperty;
use Padawan\Domain\Project\Node\InterfaceData;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;
use Padawan\Domain\Project\Collection\Specification;
use Psr\Log\LoggerInterface;

class ObjectCompleter extends AbstractInCodeBodyCompleter
{

    /** @property LoggerInterface */
    private $logger;

    /** @property ClassRepository */
    private $classRepository;

    public function __construct(
        LoggerInterface $logger,
        ClassRepository $classRepository
    ) {
        $this->logger = $logger;
        $this->classRepository = $classRepository;
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
        $class = $this->classRepository->findByName($project, $fqcn);
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
}
