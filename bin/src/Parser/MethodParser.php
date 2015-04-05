<?php

namespace Parser;

use Entity\MethodData;
use Entity\MethodParam;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Param;
use PhpParser\Node\Name;

class MethodParser{
    /** @var $useParser UseParser */
    private $useParser;

    /**
     * Constructs
     */
    public function __construct(UseParser $useParser)
    {
        $this->useParser = $useParser;
    }
    
    /**
     * Parses ClassMethod node to MethodData
     *
     * @return MethodData
     */
    public function parse(ClassMethod $node)
    {
        $method = new MethodData($node->name);
        $method->startLine = $node->getAttribute("startLine");
        $method->endLine = $node->getAttribute("endLine");
        $method->setType($node->type);
        $comments = $node->getAttribute("comments");
        foreach($node->params AS $child){
            if($child instanceof Param){
                $method->addParam($this->parseMethodArgument($child));
            }
        }
        if(is_array($comments))
            $method->doc = $comments[count($comments)-1]->getText();
        return $method;
    }
    protected function parseMethodArgument(Param $node){
        $param = new MethodParam();
        $param->name = $node->name;
        if($node->type instanceof Name)
            $param->type = $this->useParser->getFQCN($node->type);
        else{
            $param->type = $node->type;
        }
        return $param;
    }
}
