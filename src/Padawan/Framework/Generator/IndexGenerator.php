<?php

namespace Padawan\Framework\Generator;

use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Project\Node\InterfaceData;
use Padawan\Domain\Event\IndexGenerationEvent;
use Padawan\Framework\Domain\Project\InMemoryIndex;
use Padawan\Domain\Project;
use Padawan\Domain\Project\File;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Scope\FileScope;
use Padawan\Framework\Utils\PathResolver;
use Padawan\Framework\Utils\Composer;
use Padawan\Framework\Utils\ClassUtils;
use Psr\Log\LoggerInterface;
use Padawan\Parser\Walker\IndexGeneratingWalker;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Padawan\Domain\Generator\IndexGenerator as IndexGeneratorInterface;

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

    public function generateProjectIndex(Project $project, $rewrite = true)
    {
        // You know what this mean
        gc_disable();
        $index = $project->getIndex();
        $globalTime = 0;
        $done = 0;
        $files = $this->filesFinder->findProjectFiles($project);
        $projectRoot = $project->getRootDir();
        $all = count($files);
        foreach ($files as $file) {
            $start = microtime(1);
            $this->processFile($index, $file, $rewrite, $projectRoot);
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
        $filePath,
        $rewrite = true,
        $projectRoot = null
    ) {
        $this->getLogger()
            ->info("processing $filePath");
        $file = $index->findFileByPath($filePath);
        if (empty($file)) {
            $file = new File($filePath);
        }
        $filePath = $this->path->getAbsolutePath($file->path(), $projectRoot);
        $content = $this->path->read($filePath);
        $hash = sha1($content);
        if ($rewrite || $file->isChanged($hash)) {
            $scope = $this->createScopeForFile($file, $content, $index, $rewrite);
            $this->processFileScope(
                $file,
                $index,
                $scope,
                $hash
            );
        }
    }
    public function createScopeForFile(File $file, $content, Index $index, $rewrite = true)
    {
        $startParser = microtime(1);
        $walker = $this->walker;
        $parser = $this->getClassUtils()->getParser();
        $parser->addWalker($walker);
        $parser->setIndex($index);
        $scope = $parser->parseContent($file->path(), $content, null);
        $end = microtime(1) - $startParser;
        $this->getLogger()->info("Parsing: [$end]s");
        return $scope;
    }
    public function processFileScope(File $file, Index $index, FileScope $scope = null, $hash = null)
    {
        if (empty($scope)) {
            return;
        }
        if (empty($hash)) {
            throw new \Exception("Contents hash could not be empty");
        }
        $this->getLogger()->debug(sprintf("Processing %s classes", count($scope->getClasses())));
        $this->getLogger()->debug(sprintf("Processing %s interfaces", count($scope->getInterfaces())));
        $this->getLogger()->debug(sprintf("Processing %s functions", count($scope->getFunctions())));
        foreach ($scope->getClasses() as $classData) {
            $this->getLogger()->debug('Processing node ' . $classData->fqcn->toString());
        }
        foreach ($scope->getInterfaces() as $interfaceData) {
            $this->getLogger()->debug('Processing node ' . $interfaceData->fqcn->toString());
        }
        foreach ($scope->getFunctions() as $functionData) {
            $this->getLogger()->debug('Processing node ' . $functionData->name);
        }
        $file->updateScope($scope, $hash);
        $index->addFile($file);
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
