<?php

namespace Utils;

use Parser\ClassParser;

class ClassUtils{
    private $path;
    private $parser;

    public function __construct(PathResolver $path, ClassParser $parser){
        $this->path = $path;
        $this->parser = $parser;
    }
    public function getParser(){
        return $this->parser;
    }
}
