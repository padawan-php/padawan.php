<?php

namespace Entity\Completion;

class Token {
    public $symbol = "";
    public $type = 0;
    public $parent;
    public $children = [];
    public function addChild(Token $token){
        $this->children[] = $token;
        $token->parent = $this;
    }
}
