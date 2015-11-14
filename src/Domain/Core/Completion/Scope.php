<?php

namespace Domain\Core\Completion;

use Domain\Core\FQCN;
use Domain\Core\Node\Variable;
use Domain\Core\Node\Uses;
use Domain\Core\Node\FunctionData;

interface Scope
{
    /** @return Variable[] */
    public function getVars();
    /** @return Variable */
    public function getVar($varName);
    public function addVar(Variable $var);
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
