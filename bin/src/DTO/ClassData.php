<?php

namespace DTO;

class ClassData{
    public $interfaces      = [];
    public $parentClasses   = [];
    public $methods         = [];
    public $parameters      = [];
    public function toArray(){
        return get_object_vars($this);
    }
}
