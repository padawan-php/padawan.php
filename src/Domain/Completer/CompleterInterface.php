<?php

namespace Domain\Completer;

use Domain\Core\Project;
use Domain\Core\Completion\Context;

interface CompleterInterface {
    public function getEntries(Project $project, Context $context);
}
