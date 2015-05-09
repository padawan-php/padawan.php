<?php

namespace Parser;

use Entity\FQN;
use Entity\Node\Uses;
use Utils\PathResolver;
use PhpParser\Parser AS ASTGenerator;
use PhpParser\NodeTraverser AS Traverser;
use Psr\Log\LoggerInterface;
use Parser\NamespaceParser;

class Parser{

    public function __construct(
        ASTGenerator $parser,
        PathResolver $path,
        Traverser $traverser,
        UseParser $useParser,
        NamespaceParser $namespaceParser,
        LoggerInterface $logger
    ){
        $this->path             = $path;
        $this->parser           = $parser;
        $this->traverser        = $traverser;
        $this->useParser        = $useParser;
        $this->namespaceParser  = $namespaceParser;
        $this->processors       = [];
        $this->astPool          = [];
        $this->logger           = $logger;
    }
    public function parseFile($file, Uses $uses = null, $createCache=true){
        $file = $this->path->getAbsolutePath($file);
        $content = $this->path->read($file);
        return $this->parseContent($file, $content, $uses, $createCache);
    }
    public function parseContent($file, $content, Uses $uses = null, $createCache=true){
        if($createCache){
            $hash = hash('md5', $content);
            if(!array_key_exists($file, $this->astPool)){
                $this->astPool[$file] = [0,0];
            }
            list($oldHash, $ast) = $this->astPool[$file];
        }
        if(!$uses instanceof Uses){
            $uses = new Uses(new FQN);
        }
        $this->setUses($uses);
        $this->setFileInfo($uses, $file);
        $this->logger->addDebug(sprintf('Cache status: %s', (
            $createCache ? 'active' : 'disabled'
        )));
        if(!$createCache || $oldHash !== $hash || empty($ast)){
            try{
                $ast = $this->parser->parse($content);
            }
            catch(\Exception $e){
                $this->logger->addError(sprintf("Parsing failed in file %s\n", $file));
                return [];
            }
            if($createCache){
                $this->astPool[$file] = [$hash, $ast];
            }
        }
        $this->logger->addInfo(sprintf("Traversing with %s processors",
            count($this->processors)
        ));
        $this->traverser->traverse($ast);
        $nodes = $this->getResultNode();
        $this->clearProcessors();
        $this->logger->addInfo('Found ' . count($nodes) . ' nodes');
        return $nodes;
    }
    public function setUses(Uses $uses){
        $this->uses = $uses;
        $this->useParser->setUses($uses);
        $this->namespaceParser->setUses($uses);
    }
    public function getUses(){
        return $this->uses;
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

    protected function setFileInfo(Uses $uses, $file){
        foreach($this->processors AS $processor){
            $processor->setFileInfo($uses, $file);
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
    private $logger;
    /** @var NamespaceParser */
    private $namespaceParser;
    private $useParser;
    private $uses;
}
