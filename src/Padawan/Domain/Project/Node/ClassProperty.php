<?php

namespace Padawan\Domain\Project\Node;

use Padawan\Domain\Project\FQCN;

class ClassProperty
{
    public $name;
    public $modifier    = 0;
    /** @property FQCN|string $type */
    public $type        = "";
    public $defauls     = "";
    public $doc         = "";

    public function __construct($name = "", $type = "") {
        $this->name = $name;
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function setType(FQCN $fqcn) {
        $this->type = $fqcn;
    }

    public function isPublic() {
        return (bool) ($this->modifier & ClassData::MODIFIER_PUBLIC);
    }

    public function isProtected() {
        return (bool) ($this->modifier & ClassData::MODIFIER_PROTECTED);
    }

    public function isPrivate() {
        return (bool) ($this->modifier & ClassData::MODIFIER_PRIVATE);
    }

    public function isStatic() {
        return (bool) ($this->modifier & ClassData::MODIFIER_STATIC);
    }

    /**
     * @param integer $modifier
     */
    public function setModifier($modifier){
        $this->modifier = $modifier;
    }
}
