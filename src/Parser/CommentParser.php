<?php

namespace Parser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Context;
use Entity\Node\Comment;
use Entity\Node\MethodParam;
use Entity\Node\Variable;

class CommentParser {

    public function __construct(UseParser $useParser){
        $this->useParser = $useParser;
    }
    /**
     * Parses DocComment block
     *
     * @param string $doc
     * @return Comment
     */
    public function parse($doc){
        $text = $doc;
        if(is_array($doc)){
            $doc = array_shift($doc);
            $text = $doc->getText();
        }
        $comment = new Comment(
            $this->trimComment($text)
        );
        $this->parseDoc($comment, $text);

        return $comment;
    }

    /**
     * Parses doc comment and populates comment entity
     *
     * @param string $text
     */
    protected function parseDoc(Comment $comment, $text){
        $context = $this->getContext();
        $block = new DocBlock($text, $context);
        foreach($block->getTags() AS $tag){
            switch($tag->getName()){
            case "param":
                $comment->addVar(
                    $this->createMethodParam($tag)
                );
                break;
            case "var":
                $comment->addVar(
                    $this->createVar($tag)
                );
                break;
            case "return":
                $comment->setReturn(
                    $this->getFQCN($tag->getType())
                );
                break;
            }
        }
    }
    protected function createMethodParam(Tag $tag){
        $name = trim($tag->getVariableName(), '$');
        $param = new MethodParam($name);
        $param->setType($this->getFQCN($tag->getType()));
        return $param;
    }
    protected function createVar(Tag $tag){
        $name = trim($tag->getVariableName(), '$');
        $param = new Variable($name);
        $param->setType($this->getFQCN($tag->getType()));
        return $param;
    }
    /**
     * Creates FQN by type string
     *
     * @param string $type
     * @return \Entity\FQCN
     */
    protected function getFQCN($type){
        return $this->useParser->parseType($type);
    }
    /**
     * @return string
     */
    protected function trimComment($comment){
        $lines = explode("\n", $comment);
        foreach($lines AS $key => $line){
            $lines[$key] = preg_replace([
                "/^\/\**/",
                "/^ *\* */",
                "/\**\/$/"
            ], "", $line);
        }
        return implode("\n", $lines);
    }
    /**
     * @return Context
     */
    protected function getContext(){
        $uses = $this->useParser->getUses();
        $namespace = $uses->getFQCN()->toString();
        $aliases = array_map(function($fqcn){
            return $fqcn->toString();
        }, $uses->all());
        return new Context($namespace, $aliases);
    }

    /** @property UseParser */
    private $useParser;
}
