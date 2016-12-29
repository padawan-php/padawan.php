<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Project\Node\MethodData;
use Padawan\Domain\Scope;

class MethodScope extends AbstractChildScope
{
    /** @var MethodData */
    private $method;
    public function __construct(Scope $scope, MethodData $method)
    {
        parent::__construct($scope);
        $this->addVar($this->getParent()->getVar('this'));
        $this->method = $method;
        foreach ($method->arguments as $param) {
            $this->addVar($param);
        }
        $this->addTypeHints($method->inlineTypeHint);
    }
    public function getFQCN()
    {
        return $this->getParent()->getFQCN();
    }
    public function getClass()
    {
        return $this->getParent()->getClass();
    }
}
