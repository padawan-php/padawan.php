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
    public function parse(Property $node, $modifier=0)
    {
        $prop = new ClassProperty;
        $prop->name = $node->name;
        $prop->setModifier($modifier);
        $comments = $node->getAttribute("comments");
        if(is_array($comments)){
            $comment = $this->commentParser->parse(
                $comments[count($comments)-1]->getText()
            );
            $var = array_pop($comment->getVars());
            if($var->getName() === $prop->name){
                $prop->doc = $comment->getDoc();
                $prop->type = $var->getType();
            }
        }
        return $prop;
    }

    private $commentParser;
}
