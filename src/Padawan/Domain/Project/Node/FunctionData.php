<?php

namespace Padawan\Domain\Project\Node;

use Padawan\Domain\Project\FQCN;

class FunctionData
{
    public $name = "";
    public $arguments = [];
    public $return;
    public $doc = "";
    public $startLine = 0;
    public $endLine = 0;

    /**
     * @property Variable[] $variables
     */
    public $variables = [];

    public function __construct($name)
    {
        $this->name = $name;
    }
    public function getSignature()
    {
        return sprintf(
            "(%s) : %s",
            $this->getParamsStr(),
            $this->getReturnStr()
        );
    }
    public function addParam(MethodParam $param)
    {
        if (array_key_exists($param->getName(), $this->arguments)) {
            $var = $this->arguments[$param->getName()];
            if (empty($param->getType())) {
                $param->setType($var->getType());
            }
        }
        $this->arguments[$param->getName()] = $param;
    }
    public function addArgument(MethodParam $arg)
    {
        $this->addParam($arg);
    }
    public function getParamsStr()
    {
        $paramsStr = [];
        foreach ($this->arguments as $argument) {
            /** @var MethodParam $argument */
            $curParam = [];
            if ($argument->getType()) {
                if ($argument->getType() instanceof FQCN) {
                    $curParam[] = $argument->getType()->getClassName();
                } else {
                    $curParam[] = $argument->getType();
                }
            }
            $curParam[] = sprintf("$%s", $argument->getName());
            $paramsStr[] = implode(" ", $curParam);
        }
        return implode(", ", $paramsStr);
    }
    public function getReturnStr()
    {
        if ($this->return instanceof FQCN) {
            return $this->return->getClassName();
        }
        return "mixed";
    }
    public function getReturn()
    {
        return $this->return;
    }
    public function setReturn(FQCN $fqcn = null)
    {
        $this->return = $fqcn;
    }

    public function addVar(Variable $var)
    {
        if (array_key_exists($var->getName(), $this->variables)) {
            $var = $this->variables[$var->getName()];
            if (empty($var->getType())) {
                $var->setType($var->getType());
            }
        }
        $this->variables[$var->getName()] = $var;
    }
}
