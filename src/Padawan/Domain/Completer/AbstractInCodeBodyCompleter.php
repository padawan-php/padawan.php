<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Completion\Scope\FileScope;
use Padawan\Domain\Core\Completion\Scope\MethodScope;
use Padawan\Domain\Core\Completion\Scope\ClosureScope;
use Padawan\Domain\Core\Completion\Scope\FunctionScope;

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
