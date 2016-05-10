<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;

class InterfaceNameCompleter implements CompleterInterface
{
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        foreach ($project->getIndex()->getInterfaces() as $interface) {
            $fqcn = $interface->fqcn;
            $entries[] = new Entry($fqcn->toString());
        }
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        return $context->isInterfaceName();
    }
}

