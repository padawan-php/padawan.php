<?php

namespace Padawan\Domain\Core\Completion\Scope;

use Padawan\Domain\Core\Node\FunctionData;
use Padawan\Domain\Core\Completion\Scope;

class FunctionScope extends AbstractChildScope
{
    /** @var FunctionData */
    private $function;
    public function __construct(Scope $scope, FunctionData $function)
    {
        parent::__construct($scope);
        $this->function = $function;
        $scope->addFunction($function);
        foreach ($function->arguments as $param) {
            $this->addVar($param);
        }
    }
}
