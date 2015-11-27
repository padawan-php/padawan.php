<?php

namespace Padawan\Domain\Core\Completion\Scope;

use Padawan\Domain\Core\Completion\Scope;
use Padawan\Domain\Core\FQCN;
use Padawan\Domain\Core\Node\Uses;
use Padawan\Domain\Core\Node\Variable;
use Padawan\Domain\Core\Node\FunctionData;

abstract class AbstractScope implements Scope
{
    private $variables = [];
    private $functions = [];
    private $constants = [];
    /** @var Scope */
    private $parent;

    /** @return Variable[] */
    public function getVars()
    {
        return $this->variables;
    }

    /** @return Variable */
    public function getVar($varName)
    {
        if (array_key_exists($varName, $this->variables)) {
            return $this->variables[$varName];
        }
    }

    public function addVar(Variable $var)
    {
        $this->variables[$var->getName()] = $var;
    }

    public function getFunctions()
    {
        return $this->functions;
    }

    public function getFunction($functionName)
    {
        if (array_key_exists($functionName, $this->functions)) {
            return $this->functions[$functionName];
        }
    }

    public function addFunction(FunctionData $function)
    {
        $this->functions[$function->name] = $function;
    }

    /** @return string[] */
    public function getConstants()
    {
        return $this->constants;
    }

    public function getConstant($constName)
    {
        if (array_key_exists($constName, $this->constants)) {
            return $this->constants[$constName];
        }
    }

    public function addConstant($constName)
    {
        $this->constants[$constName] = $constName;
    }
}
