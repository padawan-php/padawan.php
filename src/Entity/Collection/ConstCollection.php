<?php

namespace Entity\Collection;

use Entity\Node\ClassData;

class ConstCollection
{
    /**
     * @param ClassData $class
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->map['class'] = 'class';
    }
    public function add($constant)
    {
        $this->map[$constant] = $constant;
    }
    public function all()
    {
        $consts = $this->map;
        $parent = $this->class->getParent();
        if ($parent instanceof ClassData) {
            $consts = array_merge(
                $parent->properties->all(),
                $consts
            );
        }
        sort($consts);
        return $consts;
    }
    public function get($propName)
    {
        if (array_key_exists($propName, $this->map)) {
            $const = $this->map[$propName];
            return $const;
        }
        $parent = $this->class->getParent();
        if ($parent instanceof ClassData) {
            return $parent->properties->get($propName);
        }
    }

    private $map = [];
    private $class;
}
