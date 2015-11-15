<?php

namespace Framework\Generator;

use Domain\Core\Node\ClassData;
use Domain\Core\Node\InterfaceData;
use Domain\Event\IndexGenerationEvent;
use Domain\Core\Index;
use Domain\Core\Project;
use Domain\Core\Completion\Scope\FileScope;
use Framework\Utils\PathResolver;
use Framework\Utils\Composer;
use Framework\Utils\ClassUtils;
use Psr\Log\LoggerInterface;
use Parser\Walker\IndexGeneratingWalker;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Domain\Generator\IndexGenerator as IndexGeneratorInterface;

class IndexGenerator implements IndexGeneratorInterface
{
    const BEFORE_GENERATION = 'index.before_generation';
    const AFTER_GENERATION = 'index.after_generation';

    public function __construct(
        PathResolver $path,
        ClassUtils $class,
        LoggerInterface $logger,
        EventDispatcher $dispatcher,
        FilesFinder $filesFinder,
        IndexGeneratingWalker $walker
    ) {
        $this->path             = $path;
        $this->classUtils       = $class;
        $this->logger           = $logger;
        $this->walker           = $walker;
        $this->dispatcher       = $dispatcher;
        $this->filesFinder      = $filesFinder;
    }

    public function generateIndex(Project $project)
    {
        $event = new IndexGenerationEvent($project);
        $this->dispatcher->dispatch(self::BEFORE_GENERATION, $event);

        $index = $project->getIndex();

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
        $files = $this->filesFinder->findProjectFiles($project);
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
            $this->createScopeForFile($file, $index, $createCache)
        );
        $index->addParsedFile($file);
    }
    public function createScopeForFile($file, Index $index, $createCache = true)
    {
        $startParser = microtime(1);
        $walker = $this->walker;
        $parser = $this->getClassUtils()->getParser();
        $parser->addWalker($walker);
        $parser->setIndex($index);
        $nodes = $parser->parseFile($file, null, $createCache);
        $end = microtime(1) - $startParser;
        $this->getLogger()
            ->info("Parsing: [$end]s");
        if (is_array($nodes)) {
            print_r($nodes);
            die();
        }
        return $nodes;
    }
    public function processFileScope(Index $index, FileScope $scope)
    {
        $this->getLogger()->debug(sprintf("Processing %s classes", count($scope->getClasses())));
        $this->getLogger()->debug(sprintf("Processing %s interfaces", count($scope->getInterfaces())));
        $this->getLogger()->debug(sprintf("Processing %s functions", count($scope->getFunctions())));
        foreach ($scope->getClasses() as $classData) {
            $this->getLogger()->debug('Processing node ' . $classData->fqcn->toString());
            $index->addFQCN($classData->fqcn);
            $index->addClass($classData);
        }
        foreach ($scope->getInterfaces() as $interfaceData) {
            $this->getLogger()->debug('Processing node ' . $interfaceData->fqcn->toString());
            $index->addFQCN($interfaceData->fqcn);
            $index->addInterface($interfaceData);
        }
        foreach ($scope->getFunctions() as $functionData) {
            $this->getLogger()->debug('Processing node ' . $functionData->name);
            $index->addFunction($functionData);
        }
    }

    /** @return LoggerInterface */
    public function getLogger()
    {
        return $this->logger;
    }

    public function getWalker()
    {
        return $this->walker;
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
     * @var IndexGeneratingWalker
     */
    protected $walker;

    /** @var EventDispatcher */
    protected $dispatcher;

    /** @var FilesFinder */
    protected $filesFinder;
}
