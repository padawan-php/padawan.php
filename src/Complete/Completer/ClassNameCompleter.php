<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;
use Entity\Completion\Entry;

class ClassNameCompleter implements CompleterInterface {
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        $postfix = trim("");
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
}
