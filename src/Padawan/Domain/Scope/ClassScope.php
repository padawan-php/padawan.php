<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Project\Node\InterfaceData;
use Padawan\Domain\Scope;
use Padawan\Domain\Project\Node\Variable;

class ClassScope extends AbstractChildScope
{
    /** @var ClassData */
    private $class;
    /**
     * @param Scope $scope
     * @param ClassData|InterfaceData $class
     */
    public function __construct(Scope $scope, $class)
    {
        parent::__construct($scope);
        $this->class = $class;
        $var = new Variable('this');
        $var->setType($class->fqcn);
        $this->addVar($var);
        if ($class instanceof ClassData) {
            $scope->addClass($class);
        } elseif ($class instanceof InterfaceData) {
            $scope->addInterface($class);
        } else {
            throw \Exception("Not class or interface");
        }
    }

    /**
     * @return FQCN
     */
    public function getFQCN()
    {
        return $this->class->fqcn;
    }

    public function getClass()
    {
        return $this->class;
    }

    private function addClass($class)
    {
        $this->class = $class;
    }

    private function addInterface($class)
    {
        $this->class = $class;
    }
}
