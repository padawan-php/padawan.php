<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Scope\FileScope;

/**
 * Class AbstractFileInfoCompleter
 */
abstract class AbstractFileInfoCompleter implements CompleterInterface
{

    public function canHandle(Project $project, Context $context)
    {
        $scope = $context->getScope();
        return $scope instanceof FileScope;
    }
}
