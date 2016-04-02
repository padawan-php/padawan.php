<?php

namespace Padawan\Domain\Core\Completion\Scope;

use Padawan\Domain\Core\Node\MethodData;
use Padawan\Domain\Core\Completion\Scope;

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
