<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;

class UseCompleter extends AbstractFileInfoCompleter
{
    public function getEntries(Project $project, Context $context)
    {
        $entries = [];
        $postfix = trim($context->getData());
        $index = $project->getIndex();
        $fqcns = array_merge($index->getClasses(), $index->getInterfaces());
        foreach ($fqcns as $fqcn => $class) {
            if (!empty($postfix) && strpos($fqcn, $postfix) === false) {
                continue;
            }
            $complete = str_replace($postfix, "", $fqcn);
            $entries[] = new Entry(
                $complete,
                '',
                '',
                $fqcn
            );
        }
        usort($entries, function($a, $b) {
            $aname = $a->getName();
            $bname = $b->getName();
            $strlenDiff = strlen($aname) - strlen($bname);
            if ($strlenDiff === 0) {
                return $aname > $bname;
            }
            return $strlenDiff;
        });
        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        return parent::canHandle($project, $context) && $context->isUse();
    }
}
