<?php

namespace Framework\Complete;

use Domain\Core\Project;
use Domain\Core\Completion\Scope;
use Domain\Core\Completion\Scope\FileScope;
use Domain\Core\FQN;
use Parser\Parser;
use Domain\Generator\IndexGenerator;
use Domain\Completer\CompleterFactory;
use Framework\Complete\Resolver\ContextResolver;
use Parser\Processor\FileNodesProcessor;
use Parser\Processor\ScopeProcessor;
use Psr\Log\LoggerInterface;

class CompleteEngine {
    public function __construct(
        Parser $parser,
        IndexGenerator $generator,
        ContextResolver $contextResolver,
        CompleterFactory $completer,
        FileNodesProcessor $fileProcessor,
        ScopeProcessor $scopeProcessor,
        LoggerInterface $logger
    ) {
        $this->parser           = $parser;
        $this->generator        = $generator;
        $this->contextResolver  = $contextResolver;
        $this->completerFactory = $completer;
        $this->fileProcessor    = $fileProcessor;
        $this->scopeProcessor   = $scopeProcessor;
        $this->logger           = $logger;
        $this->cachePool        = [];
    }
    public function createCompletion(
        Project $project,
        $content,
        $line,
        $column,
        $file
    ) {
        $start = microtime(1);
        $entries = [];
        if ($line) {
            list($lines,, $completionLine) = $this->prepareContent(
                $content,
                $line,
                $column
            );
            try {
                $scope = $this->processFileContent($project, $lines, $line, $file);
                if (empty($scope)) {
                    $scope = new FileScope(new FQN);
                }
                $this->logger->debug(sprintf(
                    "%s seconds for ast processing",
                    (microtime(1) - $start)
                ));
            } catch (\Exception $e) {
                $scope = new FileScope(new FQN);
            }
            $entries = $this->findEntries($project, $scope, $completionLine, $column);
            $this->logger->debug(sprintf(
                "%s seconds for entries generation",
                (microtime(1) - $start)
            ));
        } elseif (!empty($content)) {
            $this->processFileContent($project, $content, $line, $file);
        }

        return [
            "entries" => $entries,
            "context" => []
        ];
    }

    /**
     * @param string $badLine
     */
    protected function findEntries(Project $project, Scope $scope, $badLine, $column)
    {
        $context = $this->contextResolver->getContext($badLine, $project->getIndex(), $scope);
        $completer = $this->completerFactory->getCompleter($context, $project);
        if ($completer) {
            return $completer->getEntries($project, $context);
        }
        return [];
    }
    /**
     * @TODO
     * Should check for bad lines
     */
    protected function prepareContent($content, $line, $column) {
        $lines = explode(PHP_EOL, $content);
        if ($line > count($lines)) {
            $badLine = "";
        } else {
            $badLine = $lines[$line - 1];
        }
        $completionLine = substr($badLine, 0, $column - 1);
        $lines[$line - 1] = "";
        return [$lines, trim($badLine), trim($completionLine)];
    }

    /**
     * @return Scope
     */
    protected function processFileContent(Project $project, $lines, $line, $file) {
        if (is_array($lines)) {
            $content = implode("\n", $lines);
        } else {
            $content = $lines;
        }
        if (empty($content)) {
            return;
        }
        if (!array_key_exists($file, $this->cachePool)) {
            $this->cachePool[$file] = [0, [], []];
        }
        if ($this->isValidCache($file, $content)) {
            list(,, $scope) = $this->cachePool[$file];
        }
        if (empty($scope)) {
            $parser = $this->parser;
            $parser->addProcessor($this->fileProcessor);
            $nodes = $parser->parseContent($file, $content);
            $this->generator->processFileScope(
                $project->getIndex(),
                $nodes
            );
            /** @var \Domain\Core\Node\Uses */
            $uses = $parser->getUses();
            $this->scopeProcessor->setIndex($project->getIndex());
            $this->scopeProcessor->setLine($line);
            $parser->addProcessor($this->scopeProcessor);
            $scope = $parser->parseContent($file, $content, $uses);
            $contentHash = hash('sha1', $content);
            $this->cachePool[$file] = [$contentHash, $nodes, $scope];
        }
        if ($scope) {
            return $scope;
        }
        return null;
    }

    private function isValidCache($file, $content)
    {
        $contentHash = hash('sha1', $content);
        list($hash) = $this->cachePool[$file];
        return $hash === $contentHash;
    }

    /** @var Parser */
    private $parser;
    /** @property IndexGenerator */
    private $generator;
    private $contextResolver;
    private $completerFactory;
    /** @property FileNodesProcessor */
    private $fileProcessor;
    /** @property ScopeProcessor */
    private $scopeProcessor;
    private $cachePool;
    /** @var LoggerInterface */
    private $logger;
}
