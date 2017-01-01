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
    private $prefix;

    public function getEntries(Project $project, Context $context)
    {
        $this->prefix = $context->getData();
        $cursorLine = $context->getCursorLine();
        $variables = array_filter(
            $context->getScope()->getVars(),
            function(Variable $var) use ($cursorLine) {
                return $var->getStartLine() < $cursorLine;
            }
        );
        return array_map([$this, 'createEntry'], $variables);
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
            str_replace($this->prefix, '', $var->getName()),
            $type, '', $var->getName()
        );
    }
}
