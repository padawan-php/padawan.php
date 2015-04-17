<?php

namespace Entity\Collection;

use Entity\Node\ClassProperty;

class PropertiesCollection {
    private $map        = [];
    public function add(ClassProperty $prop){
        $this->map[$prop->name] = $prop;
    }
    public function toArray(){
        $map = [
            'modifier' => [
                'public' => [],
                'protected' => [],
                'private' => [],
                'static' => []
            ],
            'all' => []
        ];
        foreach($this->map AS $prop){
            if($prop->isPublic()){
                $map['modifier']['public'][] = $prop->name;
            }
            if($prop->isProtected()){
                $map['modifier']['protected'][] = $prop->name;
            }
            if($prop->isPrivate()){
                $map['modifier']['private'][] = $prop->name;
            }
            if($prop->isStatic()){
                $map['modifier']['static'][] = $prop->name;
            }
            $map['all'][$prop->name] = $prop->toArray();
        }
        return $map;
    }
}
