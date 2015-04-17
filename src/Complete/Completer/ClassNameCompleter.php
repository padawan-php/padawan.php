<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;
use Entity\Completion\Entry;

class ClassNameCompleter{
    public function getEntries(Project $project, Context $context){
        $entries = [];
        $postfix = trim($context->getPostfix());
        foreach($project->getIndex()->getClasses() as $fqcn => $class){
            if(!empty($postfix) && strpos($fqcn, $postfix) === false){
                continue;
            }
            $complete = str_replace($postfix, "", $fqcn);
            $entries[] = new Entry(
                $complete, '', '',
                $fqcn
            );
        }
        return $entries;
    }
}
