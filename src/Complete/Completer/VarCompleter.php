<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Project;
use Entity\Node\Variable;
use Entity\Completion\Entry;
use Entity\FQCN;

class VarCompleter implements CompleterInterface
{
    public function getEntries(Project $project, Context $context)
    {
        return array_map([$this, 'createEntry'], $context->getScope()->getVars());
    }

    protected function createEntry(Variable $var)
    {
        $type = $var->getType() instanceof FQCN ?
            $var->getType()->toString() : $var->getType();
        return new Entry(
            $var->getName(),
            $type
        );
    }
}
