<?php

namespace Entity\Completion\Scope;

use Entity\Completion\Scope;

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
    public function getNamespace()
    {
        return $this->parent->getNamespace();
    }
    public function getUses()
    {
        return $this->parent->getUses();
    }
}
