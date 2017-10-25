<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Completion\Context;
use Padawan\Domain\Scope;
use Padawan\Domain\Project;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Completion\Entry;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Scope\AbstractChildScope;

class GlobalCompleter extends AbstractInCodeBodyCompleter
{
    public function getEntries(Project $project, Context $context)
    {
        $scope = $context->getScope();
        do {
            $scope = $scope->getParent();
        } while ($scope instanceof AbstractChildScope);

        return array_map([$this, 'createEntry'], $scope->getVars());
    }

    public function canHandle(Project $project, Context $context)
    {
        return parent::canHandle($project, $context) && $context->isGlobal();
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
