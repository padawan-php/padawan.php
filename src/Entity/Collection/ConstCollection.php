<?php

namespace Entity\Collection;

class ConstCollection {

    public function __construct($class){
        $this->class = $class;
        $this->map['class'] = 'class';
    }
    public function add($constant){
        $this->map[$constant] = $constant;
    }
    public function all(Specification $spec = null){
        $consts = $this->map;
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            $props = array_merge(
                $parent->properties->all(),
                $consts
            );
        }
        sort($consts);
        return $consts;
    }
    public function get($propName, $spec = null){
        if(array_key_exists($propName, $this->map)){
            $const = $this->map[$propName];
            return $const;
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            return $parent->properties->get($name);
        }
    }

    private $map        = [];
    private $class;
}
