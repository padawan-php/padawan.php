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
    public function all($mode='private'){
        $props = [];
        list($public, $protected, $private, $parentMode) = $this->expandMode($mode);
        foreach($this->map AS $prop){
            if(!$public && $prop->isPublic()){
                continue;
            }
            if(!$protected && $prop->isProtected()){
                continue;
            }
            if(!$private && $prop->isPrivate()){
                continue;
            }
            $props[$prop->name] = $prop;
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            $props = array_merge(
                $parent->properties->all($parentMode, $magic),
                $props
            );
        }
        sort($props);
        return $props;
    }
    public function get($propName, $mode='private'){
        list($public, $protected, $private, $parentMode) = $this->expandMode($mode);
        if(array_key_exists($propName, $this->map)){
            return $this->map[$propName];
        }
        $parent = $this->class->getParent();
        if($parent instanceof ClassData){
            return $parent->properties->get($name, $parentMode);
        }
    }
    protected function expandMode($mode){
        if($mode === 'private'){
            $public = $protected = $private = true;
            $parentMode = 'protected';
        }
        elseif($mode === 'protected'){
            $public = $protected = true;
            $private = false;
            $parentMode = 'protected';
        }
        else {
            $protected = $private = false;
            $public = true;
            $parentMode = 'public';
        }
        return [
            $public, $protected, $private, $parentMode
        ];
    }

    private $map        = [];
    private $class;
}
