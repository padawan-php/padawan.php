<?php

namespace Entity\Completion;

class Entry {
    private $name;
    private $signature;
    private $desc;
    private $menu;
    public function __construct($name, $signature="", $desc="", $menu=""){
        $this->name             = $name;
        $this->signature        = $signature;
        $this->desc             = $desc;
        $this->menu             = $menu;
    }
    public function getName(){
        return $this->name;
    }
    public function getSignature(){
        return $this->signature;
    }
    public function getDesc(){
        return $this->desc;
    }
    public function getMenu(){
        return $this->menu;
    }
}
