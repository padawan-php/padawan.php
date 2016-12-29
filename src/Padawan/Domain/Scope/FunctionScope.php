<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Scope;

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
        $this->addTypeHints($function->inlineTypeHint);
    }
}
