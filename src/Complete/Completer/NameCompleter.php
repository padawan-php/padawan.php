<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;
use Entity\Completion\Entry;
use Entity\Node\FunctionData;

class NameCompleter implements CompleterInterface
{
    public function getEntries(Project $project, Context $context)
    {
        $entries = [];
        $postfix = trim($context->getData());
        foreach ($project->getIndex()->getFunctions() as $function) {
            /** @var FunctionData $function */
            $name = $function->name;
            if (!empty($postfix) && strpos($name, $postfix) === false) {
                continue;
            }
            $entries[$name] = new Entry($function->name, "", $function->doc);
        }
        $entries = array_values($entries);
        return $entries;
    }
}
