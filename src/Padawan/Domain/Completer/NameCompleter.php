<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Entry;
use Padawan\Domain\Core\Node\FunctionData;

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
            $entries[$name] = new Entry($function->name, $function->getSignature(), $function->doc);
        }
        $entries = array_values($entries);
        return $entries;
    }
}
