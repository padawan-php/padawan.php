<?php

namespace Entity\Collection;

use Entity\Node\MethodData;

class Specification {
    private $allowProtected;
    private $allowPrivate;
    private $showStatic;
    private $magic;
    private $parentMode;

    public function __construct($mode = 'private', $static = false, $magic = false){
        $this->expandMode($mode);
        $this->showStatic = $static;
        $this->magic = $magic;
    }
    public function satisfy($node){
        if(!$this->allowProtected && $node->isProtected()){
            return false;
        }
        if(!$this->allowPrivate && $node->isPrivate()){
            return false;
        }
        if($node instanceof MethodData){
            if($node->isMagic() && !$this->magic){
                return false;
            }
        }
        if($this->showStatic < 2 && $node->isStatic() != $this->showStatic){
            return false;
        }
        return true;
    }
    public function isStatic(){
        return $this->showStatic;
    }
    public function isMagic(){
        return $this->magic;
    }
    public function getParentMode(){
        return $this->parentMode;
    }
    protected function expandMode($mode){
        if($mode === 'private'){
            $this->allowProtected = $this->allowPrivate = true;
            $this->parentMode = 'protected';
        }
        elseif($mode === 'protected'){
            $this->allowProtected = true;
            $this->allowPrivate = false;
            $this->parentMode = 'protected';
        }
        else {
            $this->allowProtected = $this->allowPrivate = false;
            $this->parentMode = 'public';
        }
    }
}
