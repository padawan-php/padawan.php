<?php

namespace Padawan\Domain\Scope;

use Padawan\Domain\Scope;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Project\Node\TypeHint;

abstract class AbstractScope implements Scope
{
    private $variables = [];
    private $functions = [];
    private $constants = [];
    /** @var Scope */
    private $parent;

    private $typeHints = [];

    /** @return Variable[] */
    public function getVars($startLine = null)
    {
        if (!is_null($startLine) && !empty($this->typeHints)) {
            return array_merge(
                $this->variables,
                $this->_filterTypeHints($startLine)
            );
        }
        return $this->variables;
    }

    /** @return Variable */
    public function getVar($varName, $startLine = null)
    {
        if (!is_null($startLine) && !empty($this->typeHints)) {
            $candidates = array_merge(
                $this->variables,
                $this->_filterTypeHints($startLine)
            );
        } else {
            $candidates = $this->variables;
        }

        if (array_key_exists($varName, $candidates)) {
            return $candidates[$varName];
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

    public function addTypeHints($typeHints)
    {
        if (is_array($typeHints)) {
            $this->typeHints = array_values($typeHints);
        } else {
            $this->typeHints[] = $typeHints;
        }
    }

    private function _filterTypeHints($startLine)
    {
        if ($startLine < 2) {
            // PHP file header
            return [];
        }
        $result = array_filter($this->typeHints, function($th) use ($startLine) {
            /** @var $th TypeHint */
            return $th->startLine <= $startLine;
        });
        $returnVal = [];
        foreach ($result as $th) {
            $returnVal[$th->getName()] = $th;
        }

        return $returnVal;
    }
}
