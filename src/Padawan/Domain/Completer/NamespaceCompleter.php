<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;

class NamespaceCompleter extends AbstractFileInfoCompleter
{

    public function getEntries(Project $project, Context $context, $cursorLine = 0) {
        $entries = [];
        $postfix = trim($context->getData());
        foreach ($project->getIndex()->getFQCNs() AS $fqcn) {
            $namespace = $fqcn->getNamespace();
            if (!empty($postfix) && strpos($namespace, $postfix) === false) {
                continue;
            }
            $complete = str_replace($postfix, "", $namespace);
            $entries[$namespace] = new Entry($complete, "", "", $namespace);
        }
        $entries = array_values($entries);
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        return parent::canHandle($project, $context) && $context->isNamespace();
    }
}
