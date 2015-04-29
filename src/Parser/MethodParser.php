<?php

namespace Parser;

use Entity\Node\MethodData;
use Entity\Node\MethodParam;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Param;
use PhpParser\Node\Name;

class MethodParser{

    /**
     * Constructs
     *
     * @param UseParser $useParser
     */
    public function __construct(
        UseParser $useParser,
        CommentParser $commentParser
    )
    {
        $this->useParser        = $useParser;
        $this->commentParser    = $commentParser;
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
        if(is_array($comments)){
            $comment = $this->commentParser->parse(
                $comments[count($comments)-1]->getText()
            );
            $method->doc = $comment->getDoc();
            $method->return = $comment->getReturn();
        }
        return $method;
    }
    protected function parseMethodArgument(Param $node){
        $param = new MethodParam($node->name);
        if($node->type instanceof Name)
            $param->setFQCN($this->useParser->getFQCN($node->type));
        else{
            $param->setType($node->type);
        }
        return $param;
    }

    /** @var $useParser UseParser */
    private $useParser;
    private $commentParser;
}
