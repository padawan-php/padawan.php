<?php

namespace Domain\Core\Completion\Scope;

use Domain\Core\Node\MethodData;
use Domain\Core\Completion\Scope;

class MethodScope extends FunctionScope
{
    public function __construct(Scope $scope, MethodData $function)
    {
        parent::__construct($scope, $function);
        $this->addVar($this->getParent()->getVar('this'));
    }
    public function getFQCN()
    {
        return $this->getParent()->getFQCN();
    }
}
