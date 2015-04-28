<?php

namespace Parser;

use Entity\FQCN;
use Entity\Node\Uses;
use Utils\PathResolver;
use PhpParser\Parser AS ASTGenerator;
use PhpParser\NodeTraverser AS Traverser;

class Parser{

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
        $this->processors       = [];
        $this->astPool          = [];
    }
    public function parseFile(FQCN $fqcn, $file){
        $file = $this->path->getAbsolutePath($file);
        $content = $this->path->read($file);
        return $this->parseContent($fqcn, $file, $content);
    }
    public function parseContent(FQCN $fqcn, $file, $content){
        $hash = hash('md5', $content);
        if(!array_key_exists($file, $this->astPool)){
            $this->astPool[$file] = [0,0];
        }
        $uses = new Uses($this->parseFQCN($fqcn->getNamespace()));
        $this->useParser->setUses($uses);
        list($oldHash, $ast) = $this->astPool[$file];
        if($oldHash !== $hash){
            try{
                $ast = $this->parser->parse($content);
            }
            catch(\Exception $e){
                printf("Parsing failed in file %s\n", $file);
            }
            $this->astPool[$file] = [$hash, $ast];
        }
        $this->setFileInfo($fqcn, $file);
        $this->traverser->traverse($ast);
        return $this->getResultNode();
    }
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }
    public function addProcessor(Processor\ProcessorInterface $processor){
        $this->processors[] = $processor;
        $this->traverser->addVisitor($processor);
    }
    public function clearProcessors(){
        foreach($this->processors AS $processor){
            $this->traverser->removeVisitor($processor);
        }
        $this->processors = [];
    }
    public function getResultNode(){
        $nodes = [];
        foreach($this->processors as $processor){
            $nodes = array_merge($processor->getResultNodes(), $nodes);
        }
        return $nodes;
    }

    protected function setFileInfo(FQCN $fqcn, $file){
        foreach($this->processors AS $processor){
            $processor->setFileInfo($fqcn, $file);
        }
    }

    private $parsedClasses = [];
    /** @var PathResolver */
    private $path;
    /** @var PhpParser */
    private $parser;
    /** @var Traverser */
    private $traverser;
    /** @var Processor\ProcessorInterface[] */
    private $processors;
    private $astPool;
}
