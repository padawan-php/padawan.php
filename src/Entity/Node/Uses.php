<?php

namespace Entity\Node;

use Entity\FQN;
use Entity\FQCN;

class Uses {
    private $map        = [];
    private $reversed;
    private $fqcn;

    public function __construct(FQN $fqcn = null){
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

    /**
     * Finds alias for given FQCN.
     *
     * @return string
     */
    public function findAlias(FQCN $fqcn){
        $rev = $this->getReversed();
        if(array_key_exists($fqcn->toString(), $rev)){
            return $rev[$fqcn->toString()];
        }
        return $fqcn->toString();
    }

    public function getFQCN(){
        return $this->fqcn;
    }

    public function setFQCN(FQN $fqcn){
        $this->fqcn = $fqcn;
    }

    /**
     * Adds FQCN to uses map
     */
    public function add(FQCN $fqcn, $alias = null){
        if(!$alias){
            $alias = $fqcn->getClassName();
        }
        $this->map[$alias] = $fqcn;
        $this->reversed = null;
    }

    public function all(){
        return $this->map;
    }

    protected function getReversed(){
        if($this->reversed === null){
            $this->reversed = [];
            foreach($this->map AS $alias=>$fqcn){
                $this->reversed[$fqcn->toString()] = $alias;
            }
        }
        return $this->reversed;
    }
}
