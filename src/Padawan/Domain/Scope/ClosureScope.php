<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Scope;
use Padawan\Domain\Project\Node\Variable;

class ClosureScope extends AbstractChildScope
{
    /**
     * @param Scope $scope
     * @param ClassData $class
     */
    public function __construct(Scope $scope, $class = null)
    {
        parent::__construct($scope);
        if ($class !== null) {
            $var = new Variable('this');
            $var->setType($class->fqcn);
            $this->addVar($var);
        }
    }
}
