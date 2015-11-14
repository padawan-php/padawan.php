<?php

namespace Domain\Core\Completion\Scope;

use Domain\Core\Node\ClassData;
use Domain\Core\Completion\Scope;
use Domain\Core\Node\Variable;

class ClassScope extends AbstractChildScope
{
    /** @var ClassData */
    private $class;
    public function __construct(Scope $scope, ClassData $class)
    {
        parent::__construct($scope);
        $this->class = $class;
        $var = new Variable('this');
        $var->setType($class->fqcn);
        $this->addVar($var);
    }

    /**
     * @return FQCN
     */
    public function getFQCN()
    {
        return $this->class->fqcn;
    }
}
