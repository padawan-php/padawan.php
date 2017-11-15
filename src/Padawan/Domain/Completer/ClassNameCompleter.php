<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;

class ClassNameCompleter implements CompleterInterface
{
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        $postfix = trim($context->getData());
        $uses = $context->getScope()->getUses();
        foreach ($project->getIndex()->getClasses() as $fqcn => $class) {
            $fqcn = $uses ? $uses->findAlias($class->fqcn) : $class->fqcn;
            if (!empty($postfix) && strpos($fqcn, $postfix) === false) {
                continue;
            }
            $complete = str_replace($postfix, "", $fqcn);
            $entries[] = new Entry($complete, '', '', $fqcn);
        }
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        return $context->isClassName();
    }
}
