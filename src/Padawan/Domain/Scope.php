<?php

namespace Padawan\Domain;

use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\Node\FunctionData;

interface Scope
{
    /** @return Variable[] */
    public function getVars();
    /** @return Variable */
    public function getVar($varName);
    public function addVar(Variable $var);
    /** @return FQCN */
    public function getFQCN();
    /** @return FQCN */
    public function getNamespace();
    /** @return Uses */
    public function getUses();
    public function getFunctions();
    public function getFunction($functionName);
    public function addFunction(FunctionData $function);
    /** @return string[] */
    public function getConstants();
    public function getConstant($constName);
    public function addConstant($constName);
}
