<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Scope;

abstract class AbstractChildScope extends AbstractScope
{
    /** @var Scope */
    private $parent;
    public function __construct(Scope $scope)
    {
        $this->parent = $scope;
    }
    /** @return Scope */
    public function getParent()
    {
        return $this->parent;
    }
    public function getFQCN()
    {
        return $this->parent->getFQCN();
    }
    public function getNamespace()
    {
        return $this->parent->getNamespace();
    }
    public function getUses()
    {
        return $this->parent->getUses();
    }
}
