<?php

namespace Parser;

use PhpParser\Parser as ASTGenerator;

class ErrorFreePhpParser extends ASTGenerator{
    public function parse($content){
        try {
            return parent::parse($content);
        }
        catch(\Exception $e){
            return $this->semValue;
        }
    }
}
