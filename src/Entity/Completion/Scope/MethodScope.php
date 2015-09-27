<?php

namespace Entity\Completion\Scope;

use Entity\Node\MethodData;
use Entity\Completion\Scope;

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
