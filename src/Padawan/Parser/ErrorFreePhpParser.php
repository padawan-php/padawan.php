<?php

namespace Padawan\Parser;

use PhpParser\Parser\Php5 as ASTGenerator;

class ErrorFreePhpParser extends ASTGenerator {
    public function parse($content) {
        try {
            return parent::parse($content);
        }
        catch (\Exception $e) {
            return $this->semValue;
        }
    }
}
