<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Entry;
use Padawan\Domain\Core\Node\FunctionData;

class GlobalFunctionsCompleter extends AbstractInCodeBodyCompleter
{
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
