<?php

namespace Parser;

class CommentParser {
    public function parse($doc){
        if(is_array($doc)){
            return $doc[0]->getText();
        }
        return $doc;
    }
}
