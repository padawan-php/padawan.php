<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Entry;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Project\ClassRepository;
use Padawan\Domain\Project\Node\FunctionData;

class GlobalFunctionsCompleter extends AbstractInCodeBodyCompleter
{

    /** @property ClassRepository */
    private $classRepository;

    public function __construct(
        ClassRepository $classRepository
    ) {
        $this->classRepository = $classRepository;
    }

    public function getEntries(Project $project, Context $context)
    {
        $entries = [];
        $postfix = trim($context->getData());
        foreach ($project->getIndex()->getFunctions() as $function) {
            /** @var FunctionData $function */
            $name = $function->name;
            if (!empty($postfix) && strpos($name, $postfix) !== 0) {
                continue;
            }
            $nameToComplete = str_replace($postfix, "", $name);
            $entries[$name] = new Entry(
                $nameToComplete,
                $function->getSignature(),
                $function->doc,
                $name
            );
        }
        $entries = array_values($entries);
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        $postfix = "";
        if (is_string($context->getData())) {
            $postfix = trim($context->getData());
        }
        return parent::canHandle($project, $context)
            && ($context->isString() || $context->isEmpty())
            && strlen($postfix) > 0
            ;
    }
}
