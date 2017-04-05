<?php

namespace Padawan\Parser;

use Padawan\Domain\Project\FQN;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Framework\Utils\PathResolver;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser as Traverser;
use PhpParser\ErrorHandler\Collecting;
use Psr\Log\LoggerInterface;
use Padawan\Parser\NamespaceParser;

class Parser
{

    public function __construct(
        ParserFactory $parserFactory,
        Traverser $traverser,
        UseParser $useParser,
        NamespaceParser $namespaceParser,
        LoggerInterface $logger
    ) {
        $this->parserFactory    = $parserFactory;
        $this->traverser        = $traverser;
        $this->useParser        = $useParser;
        $this->namespaceParser  = $namespaceParser;
        $this->walker           = [];
        $this->astPool          = [];
        $this->logger           = $logger;
    }
    public function parseContent($file, $content, Uses $uses = null)
    {
        if (!$uses instanceof Uses) {
            $uses = new Uses(new FQN);
        }
        $this->setUses($uses);
        $this->setFileInfo($uses, $file);
        $parser = $this->parserFactory->create(ParserFactory::PREFER_PHP7, null);
        $collector = new Collecting;
        $ast = $parser->parse($content, $collector);
        if (empty($ast)) {
            $this->logger->error(sprintf("Parsing failed in file %s\n", $file));
            $this->clearWalkers();
            return;
        }
        $this->logger->addInfo(sprintf("Traversing with %s walkers",
            count($this->walkers)
        ));
        $this->traverser->traverse($ast);
        $nodes = $this->getResultScopes();
        $this->clearWalkers();
        return $nodes;
    }
    public function setUses(Uses $uses)
    {
        $this->uses = $uses;
        $this->useParser->setUses($uses);
        $this->namespaceParser->setUses($uses);
    }
    public function getUses()
    {
        return $this->uses;
    }
    public function parseFQCN($fqcn)
    {
        return $this->useParser->parseFQCN($fqcn);
    }
    public function addWalker($walker)
    {
        $this->walkers[] = $walker;
        $this->traverser->addVisitor($walker);
    }
    public function clearWalkers() {
        foreach ($this->walkers AS $walker) {
            $this->traverser->removeVisitor($walker);
        }
        $this->walkers = [];
    }
    public function getResultScopes()
    {
        $nodes = [];
        foreach ($this->walkers as $walker) {
            $nodes[] = $walker->getResultScope();
        }
        if (count($nodes) === 1) {
            return array_pop($nodes);
        }
        return $nodes;
    }
    public function setIndex($index)
    {
        foreach ($this->walkers as $walker) {
            $walker->setIndex($index);
        }
    }

    protected function setFileInfo(Uses $uses, $file)
    {
        foreach ($this->walkers as $walker) {
            $walker->updateFileInfo($uses, $file);
        }
    }

    private $parsedClasses = [];
    /** @var ParserFactory */
    private $parserFactory;
    /** @var Traverser */
    private $traverser;
    /** @var Walker\WalkerInterface[] */
    private $walker;
    private $astPool;
    private $logger;
    /** @var NamespaceParser */
    private $namespaceParser;
    private $useParser;
    private $uses;
}
