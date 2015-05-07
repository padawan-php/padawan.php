<?php

namespace Parser;

use Entity\FQN;
use Entity\FQCN;
use Entity\Node\InterfaceData;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\ClassMethod;

class InterfaceParser {

    public function __construct(MethodParser $methodParser){
        $this->methodParser = $methodParser;
    }

    /**
     * Parses Interface node to InterfaceData
     *
     * @return InterfaceData
     */
    public function parse(Interface_ $node, FQN $fqn, $file)
    {
        $fqcn = new FQCN($node->name, $fqn);
        $interace = new InterfaceData($fqcn, $file);
        foreach($node->stmts AS $child){
            if($child instanceof ClassMethod){
                $interace->addMethod($this->parseMethod($child));
            }
        }
        return $interace;
    }

    protected function parseMethod(ClassMethod $node){
        return $this->methodParser->parse($node);
    }

    /**
     * @property MethodParser
     */
    private $methodParser;
}
