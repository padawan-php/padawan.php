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
        $postfix = $this->getPostfix($context);
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
        $postfix = $this->getPostfix($context);
        return parent::canHandle($project, $context)
            && ($context->isString() || $context->isEmpty())
            && strlen($postfix) > 0
            ;
    }

    private function getPostfix(Context $context)
    {
        if (is_string($context->getData())) {
            return trim($context->getData());
        }
        if (empty($postfix)) {
            $contextData = $context->getData();
            if (is_array($contextData) && @$contextData[3] instanceof \PhpParser\Node\Arg) {
                if ($contextData[3]->value instanceof \PhpParser\Node\Expr\ConstFetch) {
                    $postfix = $contextData[3]->value->name;
                    $context->addType(Context::T_ANY_NAME);
                    return trim($postfix);
                }
            }
        }
        return '';
    }
}
