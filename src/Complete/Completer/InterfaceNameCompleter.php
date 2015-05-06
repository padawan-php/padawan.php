<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;
use Entity\Completion\Entry;

class InterfaceNameCompleter implements CompleterInterface {
    public function getEntries(Project $project, Context $context){
        $entries = [];
        foreach($project->getIndex()->getInterfaces() as $interface){
            $fqcn = $interface->fqcn;
            $entries[] = new Entry($fqcn->toString());
        }
        return $entries;
    }
}

