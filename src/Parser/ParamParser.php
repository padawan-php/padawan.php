<?php

namespace Parser;

use PhpParser\Node\Param;
use PhpParser\Node\Name;
use Entity\Node\MethodParam;

class ParamParser {
    public function __construct(UseParser $useParser){
        $this->useParser = $useParser;
    }
    public function parse(Param $node){
        $param = new MethodParam($node->name);
        if($node->type instanceof Name)
            $param->setFQCN($this->useParser->getFQCN($node->type));
        else{
            $param->setType($node->type);
        }
        return $param;
    }

    private $useParser;
}
