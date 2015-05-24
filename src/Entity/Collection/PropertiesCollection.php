<?php

namespace Entity\Collection;

use Entity\Node\ClassProperty;

class PropertiesCollection {

    public function __construct($class){
        $this->class = $class;
    }
    public function add(ClassProperty $prop){
        $this->map[$prop->name] = $prop;
    }
    public function all(Specification $spec = null){
        if($spec === null){
            $spec = new Specification;
        }
        $props = [];
        foreach($this->map AS $prop){
            if(!$spec->satisfy($prop)){
                continue;
            }
            $props[$prop->name] = $prop;
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            $props = array_merge(
                $parent->properties->all(new Specification(
                    $spec->getParentMode(),
                    $spec->isStatic(),
                    $spec->isMagic()
                )),
                $props
            );
        }
        sort($props);
        return $props;
    }
    public function get($propName, Specification $spec = null){
        if($spec === null){
            $spec = new Specification('private', 2, false);
        }
        if(array_key_exists($propName, $this->map)){
            $prop = $this->map[$propName];
            if($spec->satisfy($prop)){
                return $prop;
            }
            return null;
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            return $parent->properties->get(
                $name, new Specifiaction(
                    $spec->getParentMode(),
                    $spec->isStatic(),
                    $spec->isMagic()
                )
            );
        }
    }

    private $map        = [];
    private $class;
}
