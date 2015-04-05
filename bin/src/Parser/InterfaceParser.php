<?php

namespace Parser;

use Entity\InterfaceData;
use PhpParser\Node\Stmt\Interface_;

class InterfaceParser {
    /**
     * Parses Interface node to InterfaceData
     *
     * @return InterfaceData
     */
    public function parse(Interface_ $node, $fqcn, $file)
    {
        $data = new InterfaceData($fqcn, $file);
        return $data;
    }
    
}
