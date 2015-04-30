<?php

namespace Entity\Collection;

use Entity\Node\ClassProperty;

class PropertiesCollection {
    private $map        = [];
    public function add(ClassProperty $prop){
        $this->map[$prop->name] = $prop;
    }
    public function all(){
        return $this->map;
    }
    public function get($propName){
        if(array_key_exists($propName, $this->map)){
            return $this->map[$propName];
        }
        return null;
    }
}
