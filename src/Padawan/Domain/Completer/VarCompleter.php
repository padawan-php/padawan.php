<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Scope;
use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Node\Variable;
use Padawan\Domain\Core\Completion\Entry;
use Padawan\Domain\Core\FQCN;

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
