<?php

namespace Entity\Completion;

use Entity\FQCN;
use Entity\Node\Variable;
use Entity\Node\Uses;
use Entity\Node\FunctionData;

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
