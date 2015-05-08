<?php

namespace Parser;

use Entity\FQN;
use Entity\FQCN;
use Entity\Node\InterfaceData;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\ClassMethod;

class InterfaceParser {

    public function __construct(
        MethodParser $methodParser,
        UseParser $useParser
    ){
        $this->methodParser = $methodParser;
        $this->useParser    = $useParser;
    }

    /**
     * Parses Interface node to InterfaceData
     *
     * @return InterfaceData
     */
    public function parse(Interface_ $node, FQN $fqn, $file)
    {
        $fqcn = new FQCN($node->name, $fqn);
        $interface = new InterfaceData($fqcn, $file);
        foreach($node->extends AS $interfaceName){
            $interface->addInterface(
                $this->useParser->getFQCN($interfaceName)
            );
        }
        foreach($node->stmts AS $child){
            if($child instanceof ClassMethod){
                $interface->addMethod($this->parseMethod($child));
            }
        }
        return $interface;
    }

    protected function parseMethod(ClassMethod $node){
        return $this->methodParser->parse($node);
    }

    /** @var UseParser */
    private $useParser;
    /**
     * @property MethodParser
     */
    private $methodParser;
}
