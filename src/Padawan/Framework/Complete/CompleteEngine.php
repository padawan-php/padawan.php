<?php

namespace Padawan\Framework\Complete;

use Padawan\Domain\Project;
use Padawan\Domain\Project\File;
use Padawan\Domain\Scope;
use Padawan\Domain\Scope\FileScope;
use Padawan\Domain\Project\FQN;
use Padawan\Parser\Parser;
use Padawan\Domain\Generator\IndexGenerator;
use Padawan\Domain\Completer\CompleterFactory;
use Padawan\Framework\Complete\Resolver\ContextResolver;
use Padawan\Parser\Walker\IndexGeneratingWalker;
use Padawan\Parser\Walker\ScopeWalker;
use Psr\Log\LoggerInterface;

class CompleteEngine
{
    public function __construct(
        Parser $parser,
        IndexGenerator $generator,
        ContextResolver $contextResolver,
        CompleterFactory $completer,
        IndexGeneratingWalker $indexGeneratingWalker,
        ScopeWalker $scopeWalker,
        LoggerInterface $logger
    ) {
        $this->parser                   = $parser;
        $this->generator                = $generator;
        $this->contextResolver          = $contextResolver;
        $this->completerFactory         = $completer;
        $this->indexGeneratingWalker    = $indexGeneratingWalker;
        $this->scopeWalker              = $scopeWalker;
        $this->logger                   = $logger;
        $this->cachePool                = [];
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
            $entries = $this->findEntries($project, $scope, $completionLine, $column, $line);
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
    protected function findEntries(Project $project, Scope $scope, $badLine, $column, $cursorLine = null)
    {
        $context = $this->contextResolver->getContext($badLine, $project->getIndex(), $scope, $cursorLine);
        $completers = $this->completerFactory->getCompleters($project, $context);
        $this->logger->debug('Using completers', $completers);
        $entries = [];
        foreach($completers as $completer) {
            $entries = array_merge($entries, $completer->getEntries($project, $context, $cursorLine));
        }
        return $entries;
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
    protected function processFileContent(Project $project, $lines, $line, $filePath) {
        if (is_array($lines)) {
            $content = implode("\n", $lines);
        } else {
            $content = $lines;
        }
        if (empty($content)) {
            return;
        }
        if (!array_key_exists($filePath, $this->cachePool)) {
            $this->cachePool[$filePath] = [0, [], []];
        }
        if ($this->isValidCache($filePath, $content)) {
            list(,$fileScope, $scope) = $this->cachePool[$filePath];
        }
        $index = $project->getIndex();
        $file = $index->findFileByPath($filePath);
        $hash = sha1($content);
        if (empty($file)) {
            $file = new File($filePath);
        }
        if (empty($scope)) {
            $parser = $this->parser;
            $parser->addWalker($this->indexGeneratingWalker);
            $parser->setIndex($project->getIndex());
            $fileScope = $parser->parseContent($filePath, $content);
            $this->generator->processFileScope(
                $file,
                $project->getIndex(),
                $fileScope,
                $hash
            );
            /** @var \Padawan\Domain\Project\Node\Uses */
            $uses = $parser->getUses();
            $this->scopeWalker->setLine($line);
            $parser->addWalker($this->scopeWalker);
            $parser->setIndex($project->getIndex());
            $scope = $parser->parseContent($filePath, $content, $uses);
            $contentHash = hash('sha1', $content);
            $this->cachePool[$filePath] = [$contentHash, $fileScope, $scope];
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
    /** @property IndexGeneratingWalker */
    private $indexGeneratingWalker;
    /** @property ScopeWalker */
    private $scopeWalker;
    private $cachePool;
    /** @var LoggerInterface */
    private $logger;
}
