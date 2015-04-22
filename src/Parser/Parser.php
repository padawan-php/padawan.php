<?php

namespace Parser;

use Entity\FQCN;
use Entity\Node\Uses;
use Utils\PathResolver;
use PhpParser\Parser AS ASTGenerator;
use PhpParser\NodeTraverser AS Traverser;

class Parser{
    private $parsedClasses = [];
    /** @var PathResolver */
    private $path;
    /** @var PhpParser */
    private $parser;
    /** @var Traverser */
    private $traverser;
    /** @var Visitor\Visitor */
    private $visitor;

    public function __construct(
        ASTGenerator $parser,
        PathResolver $path,
        Traverser $traverser,
        UseParser $useParser
    ){
        $this->path             = $path;
        $this->parser           = $parser;
        $this->traverser        = $traverser;
        $this->useParser        = $useParser;
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
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }
    public function setProcessor(Processor\ProcessorInterface $visitor){
        if(!empty($this->visitor)){
            $this->traverser->removeVisitor($this->visitor);
        }
        $this->visitor = $visitor;
        $this->traverser->addVisitor($this->visitor);
    }
    public function getResultNode(){
        return $this->visitor->getResultNodes();
    }
}
