<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Scope\FileScope;
use Padawan\Domain\Scope\MethodScope;
use Padawan\Domain\Scope\ClosureScope;
use Padawan\Domain\Scope\FunctionScope;

/**
 * Class AbstractInCodeBodyCompleter
 */
abstract class AbstractInCodeBodyCompleter implements CompleterInterface
{

    public function canHandle(Project $project, Context $context)
    {
        $scope = $context->getScope();
        return $scope instanceof FileScope
            || $scope instanceof FunctionScope
            || $scope instanceof MethodScope
            || $scope instanceof ClosureScope;
    }
}
