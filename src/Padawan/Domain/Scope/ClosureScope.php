<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Scope;

class ClosureScope extends AbstractChildScope
{
    public function __construct(Scope $scope)
    {
        parent::__construct($scope);
        // add $this as a variable
        $thisVar = $scope->getVar('this');
        if (!empty($thisVar)) {
            $this->addVar($thisVar);
        }
    }
}
