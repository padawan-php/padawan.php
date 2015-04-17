<?php

namespace Parser;

use Entity\FQCN;
use Entity\Node\ClassData;
use Entity\Node\Uses;
use Utils\PathResolver;
use PhpParser\Parser AS ASTGenerator;
use PhpParser\NodeTraverser AS Traverser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Use_;

class Parser{
    private $parsedClasses = [];
    /** @var PathResolver */
    private $path;
    /** @var PhpParser */
    private $parser;
    /** @var ClassParser */
    private $classParser;
    /** @var InterfaceParser */
    private $interfaceParser;
    /** @var UseParser */
    private $useParser;
    /** @var Traverser */
    private $traverser;
    /** @var Visitor\Visitor */
    private $visitor;
    /** @var ClassData|InterfaceData|array */
    private $resultNode;

    public function __construct(
        ASTGenerator $parser,
        ClassParser $classParser,
        InterfaceParser $interfaceParser,
        UseParser $useParser,
        PathResolver $path,
        Visitor\Visitor $visitor,
        Traverser $traverser
    ){
        $this->path             = $path;
        $this->parser           = $parser;
        $this->classParser      = $classParser;
        $this->interfaceParser  = $interfaceParser;
        $this->useParser        = $useParser;
        $this->traverser        = $traverser;
        $this->setVisitor($visitor);
    }
    public function parseFile(FQCN $fqcn, $file){
        $file = $this->path->getAbsolutePath($file);
        $content = $this->path->read($file);
        return $this->parseContent($fqcn, $file, $content);
    }
    public function parseContent(FQCN $fqcn, $file, $content){
        try{
            $uses = new Uses($this->parseFQCN($fqcn->getNamespace()));
            $this->useParser->setUses($uses);
            $ast = $this->parser->parse($content);

            $this->visitor->setFileInfo($fqcn, $file);
            $this->traverser->traverse($ast);
        }
        catch(\Exception $e){
            printf("Parsing failed in file %s\n", $file);
        }
        return $this->getResultNode();
    }
    public function parseInterface(Interface_ $node, $fqcn, $file){
        $this->setResultNode(
            $this->interfaceParser->parse($node, $fqcn, $file)
        );
    }
    public function parseClass(Class_ $node, $fqcn, $file){
        $this->setResultNode(
            $this->classParser->parse($node, $fqcn, $file)
        );
    }
    public function parseUse(Use_ $node, $fqcn, $file){
        $this->useParser->parse($node, $fqcn, $file);
    }
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }
    public function setVisitor(Visitor\Visitor $visitor){
        $this->visitor = $visitor;
        $this->visitor->setParser($this);
        $this->traverser->addVisitor($this->visitor);
    }
    public function getResultNode(){
        return $this->resultNode;
    }
    protected function setResultNode($resultNode){
        if(!$resultNode){
            return;
        }
        $resultNode->uses = $this->useParser->getUses();
        if($this->resultNode && !is_array($this->resultNode)){
            $this->resultNode = [ $this->resultNode ];
        }
        if(is_array($this->resultNode)){
            $this->resultNode[] = $resultNode;
        }
        else {
            $this->resultNode = $resultNode;
        }
    }
}
