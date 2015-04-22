<?php

namespace Parser\Processor;

use Entity\FQCN;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Use_;
use Parser\ClassParser;
use Parser\InterfaceParser;
use Parser\UseParser;

class IndexProcessor extends NodeVisitorAbstract implements ProcessorInterface {
    public function __construct(
        ClassParser $classParser,
        InterfaceParser $interfaceParser,
        UseParser $useParser
    ){
        $this->classParser = $classParser;
        $this->interfaceParser = $interfaceParser;
        $this->useParser = $useParser;
    }
    public function setFileInfo(FQCN $fqcn, $file){
        $this->fqcn = $fqcn;
        $this->file = $file;
    }
    public function parseInterface(Interface_ $node, $fqcn, $file){
        $this->addResultNode(
            $this->interfaceParser->parse($node, $fqcn, $file)
        );
    }
    public function parseClass(Class_ $node, $fqcn, $file){
        $this->addResultNode(
            $this->classParser->parse($node, $fqcn, $file)
        );
    }
    public function parseUse(Use_ $node, $fqcn, $file){
        $this->useParser->parse($node, $fqcn, $file);
    }
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }
    public function enterNode(Node $node){
        if($node instanceof Use_){
            $this->parseUse($node, $this->fqcn, $this->file);
        }
    }
    public function leaveNode(Node $node){
        if($node instanceof Class_){
            $this->parseClass($node, $this->fqcn, $this->file);
        }
        elseif($node instanceof Interface_){
            $this->parseInterface($node, $this->fqcn, $this->file);
        }
    }
    public function clearResultNodes(){
        $this->resultNodes = [];
    }
    public function getResultNodes(){
        return $this->resultNodes;
    }
    protected function addResultNode($resultNode){
        if(!$resultNode){
            return;
        }
        $resultNode->uses = $this->useParser->getUses();
        $this->resultNodes[] = $resultNode;
    }

    private $fqcn;
    private $file;
    private $parser;
    /** @var ClassParser */
    private $classParser;
    /** @var InterfaceParser */
    private $interfaceParser;
    /** @var UseParser */
    private $useParser;
    /** @var ClassData[]|InterfaceData[] */
    private $resultNodes;
}
