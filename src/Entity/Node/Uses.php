<?php

namespace Entity\Node;

use Entity\FQCN;

class Uses {
    private $map        = [];
    private $fqcn;

    public function __construct(FQCN $fqcn = null){
        $this->fqcn = $fqcn;
    }
    /**
     * Finds FQCN in file uses. Returns false if there are no such class in uses
     *
     * @return FQCN|bool
     */
    public function find($alias){
        if(array_key_exists($alias, $this->map)){
            return $this->map[$alias];
        }
        return false;
    }

    public function getFQCN(){
        return $this->fqcn;
    }

    /**
     * Adds FQCN to uses map
     */
    public function add(FQCN $fqcn, $alias = null){
        if(!$alias){
            $alias = $fqcn->getClassName();
        }
        $this->map[$alias] = $fqcn;
    }

    public function toArray(){
        $map = [
            "uses" => [],
            "alias" => [],
            "file" => $this->fqcn->toString()
        ];
        foreach($this->map AS $alias=>$fqcn){
            $map["uses"][$alias] = $fqcn->getNamespace();
        }
        return $map;
    }
}
