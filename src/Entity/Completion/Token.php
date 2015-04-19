<?php

namespace Entity\Completion;

class Token {
    public $symbol = "";
    public $type = 0;
    public $parent;
    public $children = [];
    public function updateSymbol(){
        if(!empty($this->symbol)){
            $this->prefix = $this->symbol . $this->postfix;
        }
        $this->symbol = $this->postfix;
        $this->postfix = "";
    }
    public function addChild(Token $token){
        $this->children[] = $token;
        $token->parent = $this;
    }
}
