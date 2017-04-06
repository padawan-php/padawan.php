<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;

class ClassNameCompleter extends AbstractInCodeBodyCompleter
{
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        $postfix = $this->getPostfix($context);
        foreach ($project->getIndex()->getClasses() as $fqcn => $class) {
            if (!empty($postfix) && strpos($fqcn, $postfix) === false) {
                continue;
            }
            $fqcn = $context->getScope()->getUses()->findAlias($class->fqcn);
            $complete = str_replace($postfix, "", $fqcn);
            $entries[] = new Entry(
                $complete, '', '',
                $fqcn
            );
        }
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        $postfix = $this->getPostfix($context);
        return $context->isClassName()
            || (
                parent::canHandle($project, $context)
                && ($context->isString() || $context->isEmpty())
                && strlen($postfix) > 0
            );
    }

    private function getPostfix(Context $context)
    {
        if (is_string($context->getData())) {
            return trim($context->getData());
        }
        return "";
    }
}
