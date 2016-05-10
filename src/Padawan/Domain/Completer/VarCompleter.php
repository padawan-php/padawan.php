<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Completion\Context;
use Padawan\Domain\Scope;
use Padawan\Domain\Project;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Completion\Entry;
use Padawan\Domain\Project\FQCN;

class VarCompleter extends AbstractInCodeBodyCompleter
{
    public function getEntries(Project $project, Context $context)
    {
        return array_map([$this, 'createEntry'], $context->getScope()->getVars());
    }

    public function canHandle(Project $project, Context $context)
    {
        return parent::canHandle($project, $context) && $context->isVar();
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
