<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;

class InterfaceNameCompleter implements CompleterInterface
{
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        $postfix = trim($context->getData());
        $uses = $context->getScope()->getUses();
        foreach ($project->getIndex()->getInterfaces() as $interface) {
            $fqcn = $uses ? $uses->findAlias($interface->fqcn) : $interface->fqcn;
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
        return $context->isInterfaceName();
    }
}

