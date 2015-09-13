<?php

namespace Generator;

use Entity\Node\ClassData;
use Entity\Node\InterfaceData;
use Entity\Index;
use Entity\Project;
use Utils\PathResolver;
use Utils\Composer;
use Utils\ClassUtils;
use Psr\Log\LoggerInterface;
use Parser\Processor\IndexProcessor;
use Symfony\Component\EventDispatcher\EventDispatcher;

class IndexGenerator
{
    const BEFORE_GENERATION = 'index.before_generation';
    const AFTER_GENERATION = 'index.after_generation';

    public function __construct(
        PathResolver $path,
        Composer $composer,
        ClassUtils $class,
        LoggerInterface $logger,
        IndexProcessor $processor,
        EventDispatcher $dispatcher,
        FilesFinder $filesFinder,
        $verbose = false
    ) {
        $this->path             = $path;
        $this->composer         = $composer;
        $this->classUtils       = $class;
        $this->logger           = $logger;
        $this->verbose          = $verbose;
        $this->processor        = $processor;
        $this->dispatcher       = $dispatcher;
        $this->filesFinder      = $filesFinder;
    }

    public function generateIndex(Project $project)
    {
        $event = new IndexGenerationEvent($project);
        $this->dispatcher->dispatch(self::BEFORE_GENERATION, $event);

        $index = $project->getIndex();
        $this->populateClassMapIndex($project);

        $this->generateProjectIndex($project);

        $this->dispatcher->dispatch(self::AFTER_GENERATION, $event);

        return $index;
    }

    public function generateProjectIndex(Project $project)
    {
        // You know what this mean
        gc_disable();
        $index = $project->getIndex();
        $globalTime = 0;
        $done = 0;
        $files = $this->filesFinder->getProjectFiles($project);
        $all = count($files);
        foreach ($files as $file) {
            $start = microtime(1);
            $this->processFile($index, $file, false, false);
            $end = microtime(1) - $start;

            $this->getLogger()->debug("Indexing: [$end]s");
            $this->getLogger()->debug("Memory: " . memory_get_usage());
            $globalTime += $end;
            ++$done;
            $process = floor($done / $all * 100);
            $this->getLogger()->info("Progress: $process%");
        }
        $this->getLogger()->info("[ $globalTime ]");
        gc_enable();
    }

    public function processFile(
        Index $index,
        $file,
        $rewrite = false,
        $createCache = true
    ) {
        $this->getLogger()
            ->info("processing $file");
        if ($index->isParsed($file) && !$rewrite) {
            return;
        }
        $this->processFileScope(
            $index,
            $this->createScopeForFile($file, $createCache)
        );
        $index->addParsedFile($file);
    }
    public function createScopeForFile($file, $createCache = true)
    {
        $startParser = microtime(1);
        $processor = $this->processor;
        $processor->clearResultNodes();
        $parser = $this->getClassUtils()->getParser();
        $parser->addProcessor($processor);
        $nodes = $parser->parseFile($file, null, $createCache);
        $end = microtime(1) - $startParser;
        $this->getLogger()
            ->info("Parsing: [$end]s");
        return $nodes;
    }
    public function processFileScope(Index $index, $nodes)
    {
        $this->getLogger()->debug('Processing nodes ' . count($nodes));
        foreach ($nodes as $node) {
            if ($node instanceof ClassData) {
                $this->getLogger()->debug('Processing node ' . $node->fqcn->toString());
                $index->addFQCN($node->fqcn);
                $index->addClass($node);
            } elseif ($node instanceof InterfaceData) {
                $this->getLogger()->debug('Processing node ' . $node->fqcn->toString());
                $index->addFQCN($node->fqcn);
                $index->addInterface($node);
            }
        }
    }

    /** @return LoggerInterface */
    public function getLogger()
    {
        return $this->logger;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    protected function populateClassMapIndex(Project $project)
    {
        $classMap = $this->getComposerUtils()->getCanonicalClassMap($project->getRootDir());
        $index = $project->getIndex();
        $index->setClassMap($classMap);
    }

    public function getComposerUtils()
    {
        return $this->composer;
    }

    /** @return ClassUtils */
    public function getClassUtils()
    {
        return $this->classUtils;
    }

    public function getNamespaceUtils()
    {
        return $this->namespaceUtils;
    }

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
     * @var Composer
     */
    protected $composer;

    /**
     * Object for work with class-information
     *
     * @var ClassUtils
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

    /** @var EventDispatcher */
    protected $dispatcher;

    /** @var FilesFinder */
    protected $filesFinder;
}
