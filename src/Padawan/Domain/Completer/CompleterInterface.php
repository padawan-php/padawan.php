<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Completion\Context;

interface CompleterInterface {
    public function getEntries(Project $project, Context $context);
    public function canHandle(Project $project, Context $context);
}
