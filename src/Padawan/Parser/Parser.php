<?php

namespace Padawan\Parser;

use Padawan\Domain\Core\FQN;
use Padawan\Domain\Core\Node\Uses;
use Padawan\Framework\Utils\PathResolver;
use PhpParser\Parser as ASTGenerator;
use PhpParser\NodeTraverser as Traverser;
use Psr\Log\LoggerInterface;
use \Padawan\Parser\NamespaceParser;

class Parser {

    public function __construct(
        ASTGenerator $parser,
        PathResolver $path,
        Traverser $traverser,
        UseParser $useParser,
        NamespaceParser $namespaceParser,
        LoggerInterface $logger
    ) {
        $this->path             = $path;
        $this->parser           = $parser;
        $this->traverser        = $traverser;
        $this->useParser        = $useParser;
        $this->namespaceParser  = $namespaceParser;
        $this->walker           = [];
        $this->astPool          = [];
        $this->logger           = $logger;
    }
    public function parseFile($file, Uses $uses = null, $createCache = true)
    {
        $file = $this->path->getAbsolutePath($file);
        $content = $this->path->read($file);
        return $this->parseContent($file, $content, $uses, $createCache);
    }
    public function parseContent($file, $content, Uses $uses = null, $createCache = true)
    {
        if ($createCache) {
            $hash = hash('md5', $content);
            if (!array_key_exists($file, $this->astPool)) {
                $this->astPool[$file] = [0, 0];
            }
            list($oldHash, $ast) = $this->astPool[$file];
        }
        if (!$uses instanceof Uses) {
            $uses = new Uses(new FQN);
        }
        $this->setUses($uses);
        $this->setFileInfo($uses, $file);
        $this->logger->addDebug(sprintf('Cache status: %s', (
            $createCache ? 'active' : 'disabled'
        )));
        if (!$createCache || $oldHash !== $hash || empty($ast)) {
            try {
                $ast = $this->parser->parse($content);
            }
            catch (\Exception $e) {
                $this->logger->addError(sprintf("Parsing failed in file %s\n", $file));
                $this->logger->error($e);
                $this->clearWalkers();
                return null;
            }
            if ($createCache) {
                $this->astPool[$file] = [$hash, $ast];
            }
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
    /** @var PathResolver */
    private $path;
    /** @var PhpParser */
    private $parser;
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
