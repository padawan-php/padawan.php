<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Project;
use Entity\Node\Variable;
use Entity\Completion\Entry;

class VarCompleter implements CompleterInterface
{
    public function getEntries(Project $project, Context $context)
    {
        return array_map([$this, 'createEntry'], $context->getScope()->getVars());
    }

    protected function createEntry(Variable $var)
    {
        return new Entry(
            $var->getName()
        );
    }
}
