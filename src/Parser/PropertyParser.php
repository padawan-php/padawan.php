<?php

namespace Parser;

use Entity\Node\ClassProperty;
use PhpParser\Node\Stmt\PropertyProperty as Property;

class PropertyParser{

    public function __construct(CommentParser $commentParser){
        $this->commentParser = $commentParser;
    }

    /**
     * Parses Property node to ClassProperty
     *
     * @param $node Property
     *
     * @return ClassProperty
     */
    public function parse(Property $node, $modifier=0, $comments = null)
    {
        $prop = new ClassProperty;
        $prop->name = $node->name;
        $prop->setModifier($modifier);
        if(empty($comments)){
            $comments = $node->getAttribute("comments");
        }
        $comment = $this->commentParser->parse(
            $comments
        );
        $var = $comment->getProperty($prop->name);
        if(!empty($var)){
            $prop->doc = $comment->getDoc();
            $prop->type = $var->getType();
        }
        return $prop;
    }

    /**
     * @var CommentParser
     */
    private $commentParser;
}
