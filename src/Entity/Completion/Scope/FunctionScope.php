<?php

namespace Entity\Completion\Scope;

use Entity\Node\FunctionData;
use Entity\Completion\Scope;

class FunctionScope extends AbstractChildScope
{
    /** @var FunctionData */
    private $function;
    public function __construct(Scope $scope, FunctionData $function)
    {
        parent::__construct($scope);
        $this->function = $function;
        foreach ($function->arguments as $param) {
            $this->addVar($param);
        }
    }
}
