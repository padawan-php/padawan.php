<?php

namespace Padawan\Parser;

use PhpParser\Parser\Php5 as ASTGenerator;
use PhpParser\ErrorHandler;

class ErrorFreePhpParser extends ASTGenerator {
    public function parse($content, ErrorHandler $errorHandler = NULL) {
        try {
            return parent::parse($content);
        }
        catch (\Exception $e) {
            return $this->semValue;
        }
    }
}
