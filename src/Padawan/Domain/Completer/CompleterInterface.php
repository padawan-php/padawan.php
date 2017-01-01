<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;

interface CompleterInterface {
    public function getEntries(Project $project, Context $context, $cursorLine = 0);
    public function canHandle(Project $project, Context $context);
}
