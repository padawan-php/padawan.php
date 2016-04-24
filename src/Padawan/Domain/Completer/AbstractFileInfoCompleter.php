<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Scope\FileScope;

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
