<?php

namespace Complete\Completer;

use Entity\Project;
use Entity\Completion\Context;

interface CompleterInterface {
    public function getEntries(Project $project, Context $context);
}
