<?php

namespace Entity\Completion;

class Token {
    public $symbol = "";
    public $prefix = "";
    public $postfix = "";
    public function updateSymbol(){
        if(!empty($this->symbol)){
            $this->prefix = $this->symbol . $this->postfix;
        }
        $this->symbol = $this->postfix;
        $this->postfix = "";
    }
}
