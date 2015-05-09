<?php

namespace Generator;

use Entity\Node\ClassData;
use Entity\Node\InterfaceData;
use Entity\Index;
use Entity\Project;
use Entity\FQCN;
use Utils\PathResolver;
use Utils\Composer;
use Utils\ClassUtils;
use Psr\Log\LoggerInterface;
use Parser\Processor\IndexProcessor;

class IndexGenerator
{
    /**
     * Array of plugin classes
     * @var array
     */
    public $plugins;

    /**
     * Verbosity
     *
     * @var bool
     */
    private $verbose;

    /**
     *
     *
     * @var PathResolver
     */
    protected $path;

    /**
     * Object with Composer-specific functions
     *
     * @var Utils\ComposerUtils
     */
    protected $composer;

    /**
     * Object for work with class-information
     *
     * @var Utils\ClassUtils
     */
    protected $class;


    /**
     *
     * @var LoggerInterface
     */
    protected $logger;


    /**
     *
     * @var IndexProcessor
     */
    protected $processor;

    public function __construct(
        PathResolver $path,
        Composer $composer,
        ClassUtils $class,
        LoggerInterface $logger,
        IndexProcessor $processor,
        $verbose = false
    )
    {
        $this->path             = $path;
        $this->composer         = $composer;
        $this->classUtils       = $class;
        $this->logger           = $logger;
        $this->plugins          = array();
        $this->verbose          = $verbose;
        $this->processor        = $processor;
    }

    public function getComposerUtils(){
        return $this->composer;
    }

    public function getClassUtils(){
        return $this->classUtils;
    }

    public function getNamespaceUtils(){
        return $this->namespaceUtils;
    }

    public function generateIndex(Project $project)
    {
        $index = $project->getIndex();
        $this->populateClassMapIndex($project);

        $this->generateProjectIndex($index);

        return $index;
    }

    public function generateProjectIndex(Index $index){
        // You know what this mean
        gc_disable();
        $classMap = $index->getClassMap();
        $globalTime = 0;
        $process = 0;
        $done = 0;
        $all = count($classMap);
        foreach($index->getClassMap() as $fqcn => $file) {
            $start = microtime(1);
            $this->processFile($index, $file, false, false);
            $end = microtime(1) - $start;

            $this->getLogger()->addDebug("Indexing: [$end]s");
            $this->getLogger()->addDebug("Memory: ". memory_get_usage());
            $globalTime += $end;
            ++$done;
            $process = floor($done/$all * 100);
            $this->getLogger()->addInfo("Progress: $process%");
        }
        $this->getLogger()->addInfo("[ $globalTime ]");
        gc_enable();
    }

    public function processFile(Index $index, $file,
        $rewrite=false, $createCache=true
    ){
        $this->getLogger()
            ->addInfo("processing $file");
        if($index->isParsed($file) && !$rewrite){
            return;
        }
        $startParser = microtime(1);
        $processor = $this->processor;
        $processor->clearResultNodes();
        $parser = $this->getClassUtils()->getParser();
        $parser->addProcessor($processor);
        $nodes = $this->getClassUtils()->getParser()
            ->parseFile($file, null, $createCache);
        $end = microtime(1) - $startParser;
        $this->getLogger()
            ->addInfo("Parsing: [$end]s");
        $this->processFileNodes($index, $nodes);
        $index->addParsedFile($file);
    }
    public function processFileNodes(Index $index, $nodes){
        $this->getLogger()->addDebug('Processing nodes ' . count($nodes));
        foreach($nodes as $node){
            if($node instanceof ClassData){
                $this->getLogger()->addDebug('Processing node ' . $node->fqcn->toString());
                $index->addFQCN($node->fqcn);
                $index->addClass($node);
            }
            elseif($node instanceof InterfaceData){
                $this->getLogger()->addDebug('Processing node ' . $node->fqcn->toString());
                $index->addFQCN($node->fqcn);
                $index->addInterface($node);
            }
        }
    }

    public function getLogger(){
        return $this->logger;
    }

    protected function populateClassMapIndex(Project $project){
        $classMap = $this->getComposerUtils()->getCanonicalClassMap($project->getRootDir());
        $index = $project->getIndex();
        $index->setClassMap($classMap);
    }
}

