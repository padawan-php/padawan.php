<?php

namespace Parser\Processor;

use Entity\FQCN;
use Entity\Node\Uses;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Namespace_;
use Parser\ClassParser;
use Parser\InterfaceParser;
use Parser\UseParser;
use Parser\NamespaceParser;

class IndexProcessor extends NodeVisitorAbstract implements ProcessorInterface {
    public function __construct(
        ClassParser $classParser,
        InterfaceParser $interfaceParser,
        UseParser $useParser,
        NamespaceParser $namespaceParser
    ){
        $this->classParser = $classParser;
        $this->interfaceParser = $interfaceParser;
        $this->useParser = $useParser;
        $this->namespaceParser = $namespaceParser;
    }
    public function setFileInfo(Uses $uses, $file){
        $this->uses = $uses;
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
    public function parseUse(Use_ $node){
        $this->useParser->parse($node);
    }
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }
    public function enterNode(Node $node){
        if($node instanceof Use_){
            $this->parseUse($node);
        }
        elseif($node instanceof Namespace_){
            $this->namespaceParser->parse($node);
        }
    }
    public function leaveNode(Node $node){
        if($node instanceof Class_){
            $this->parseClass($node, $this->uses->getFQCN(), $this->file);
        }
        elseif($node instanceof Interface_){
            $this->parseInterface($node, $this->uses->getFQCN(), $this->file);
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
        $this->resultNodes[] = $resultNode;
    }

    /** @var Uses */
    private $uses;
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
    /** @var NamespaceParser */
    private $namespaceParser;
}
