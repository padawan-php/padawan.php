<?php

namespace Entity\Completion\Scope;

use Entity\Node\ClassData;
use Entity\Completion\Scope;
use Entity\Node\Variable;

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
}
