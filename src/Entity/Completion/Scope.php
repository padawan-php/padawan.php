<?php

namespace Entity\Completion;

use Entity\FQCN;
use Entity\Node\Variable;
use Entity\Node\Uses;

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
    public function getFunction();
    public function addFunction($function);
    /** @return string[] */
    public function getConstants();
    public function getConstant($constName);
    public function addConstant($constName);
}
