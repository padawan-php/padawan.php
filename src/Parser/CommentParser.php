<?php

namespace Parser;

use Sami\Parser\DocBlockParser;

class CommentParser {

    private $docCommentParser;

    public function __construct(DocBlockParser $docCommentParser){
        $this->docCommentParser = $docCommentParser;
    }
    public function parse($doc){
        if(is_array($doc)){
            $doc = array_shift($doc);
            return $doc->getText();
        }
        return $doc;
    }
    protected function trimComment($comment){
        $lines = explode("\n", $comment);
        foreach($lines AS $key => $line){
            $lines[$key] = preg_replace([
                "/^\/\**/",
                "/^ *\*/"
            ], "", $line);
        }
    }
}
