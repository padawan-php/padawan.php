<?php

namespace Domain\Completer;

use Domain\Core\Completion\Context;
use Domain\Core\Completion\Scope;
use Domain\Core\Project;
use Domain\Core\Node\Variable;
use Domain\Core\Completion\Entry;
use Domain\Core\FQCN;

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
