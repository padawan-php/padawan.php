<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Entry;

class InterfaceNameCompleter implements CompleterInterface {
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        foreach ($project->getIndex()->getInterfaces() as $interface) {
            $fqcn = $interface->fqcn;
            $entries[] = new Entry($fqcn->toString());
        }
        return $entries;
    }
}

