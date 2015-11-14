<?php

namespace Domain\Core\Node;

use Domain\Core\FQCN;

class MethodData extends FunctionData
{
    public $name        = "";
    public $arguments   = [];
    public $doc         = "";
    public $type        = 0;
    public $startLine   = 0;
    public $endLine     = 0;
    public $return      = null;

    public function isPublic()
    {
        return (bool) ($this->type & ClassData::MODIFIER_PUBLIC);
    }

    public function isProtected()
    {
        return (bool) ($this->type & ClassData::MODIFIER_PROTECTED);
    }

    public function isPrivate()
    {
        return (bool) ($this->type & ClassData::MODIFIER_PRIVATE);
    }

    public function isAbstract()
    {
        return (bool) ($this->type & ClassData::MODIFIER_ABSTRACT);
    }

    public function isFinal()
    {
        return (bool) ($this->type & ClassData::MODIFIER_FINAL);
    }

    public function isStatic()
    {
        return (bool) ($this->type & ClassData::MODIFIER_STATIC);
    }
    public function isMagic()
    {
        return in_array($this->name, [
            '__construct',
            '__destruct',
            '__get',
            '__set',
            '__isset',
            '__unset',
            '__toString',
            '__call',
            '__clone'
        ]);
    }
    public function setType($type)
    {
        $this->type = $type;
    }
}
