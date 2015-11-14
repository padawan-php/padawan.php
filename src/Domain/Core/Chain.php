<?php

namespace Domain\Core;

class Chain
{
    public function __construct(Chain $child = null, $name = "", $type = "")
    {
        $this->child = $child;
        $this->name = $name;
        $this->type = $type;
        if ($child instanceof Chain) {
            $child->setParent($this);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Chain
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Chain
     */
    public function getChild()
    {
        return $this->child;
    }

    public function setParent(Chain $parent)
    {
        $this->parent = $parent;
    }

    private $type;
    private $name;
    private $parent;
    private $child;
}
