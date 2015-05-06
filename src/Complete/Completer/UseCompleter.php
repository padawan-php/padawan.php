<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;
use Entity\Completion\Entry;

class UseCompleter implements CompleterInterface {
    public function getEntries(Project $project, Context $context){
        $entries = [];
        $postfix = trim($context->getData());
        $index = $project->getIndex();
        $fqcns = array_merge($index->getClasses(), $index->getInterfaces());
        foreach($fqcns as $fqcn => $class){
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
