<?php

namespace Parser\Processor;

use Domain\Core\FQCN;
use Domain\Core\FQN;
use Domain\Core\Node\Uses;
use Domain\Core\Completion\Scope\FileScope;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Function_;
use Parser\ClassParser;
use Parser\InterfaceParser;
use Parser\UseParser;
use Parser\NamespaceParser;
use Parser\Transformer\FunctionTransformer;

class FileNodesProcessor extends NodeVisitorAbstract implements ProcessorInterface
{
    public function __construct(
        ClassParser $classParser,
        InterfaceParser $interfaceParser,
        UseParser $useParser,
        NamespaceParser $namespaceParser,
        FunctionTransformer $functionTransformer
    ) {
        $this->classParser = $classParser;
        $this->interfaceParser = $interfaceParser;
        $this->useParser = $useParser;
        $this->namespaceParser = $namespaceParser;
        $this->fileScope = new FileScope(new FQN);
        $this->functionTransformer = $functionTransformer;
    }
    public function setFileInfo(Uses $uses, $file) {
        $this->file = $file;
        $this->fileScope = new FileScope($uses->getFQCN(), $uses);
    }
    public function parseInterface(Interface_ $node, $fqcn, $file) {
        $this->fileScope->addInterface(
            $this->interfaceParser->parse($node, $fqcn, $file)
        );
    }
    public function parseClass(Class_ $node, $fqcn, $file) {
        $this->fileScope->addClass(
            $this->classParser->parse($node, $fqcn, $file)
        );
    }
    public function parseFunction(Function_ $node) {
        $this->fileScope->addFunction(
            $this->functionTransformer->tranform($node)
        );
    }
    public function parseUse(Use_ $node) {
        $this->useParser->parse($node);
    }
    public function parseFQCN($fqcn) {
        return $this->useParser->parseFQCN($fqcn);
    }
    public function enterNode(Node $node) {
        if ($node instanceof Use_) {
            $this->parseUse($node);
        } elseif ($node instanceof Namespace_) {
            $this->namespaceParser->parse($node);
        }
    }
    public function leaveNode(Node $node) {
        $uses = $this->fileScope->getUses();
        if ($node instanceof Class_) {
            $this->parseClass($node, $uses->getFQCN(), $this->file);
        } elseif ($node instanceof Interface_) {
            $this->parseInterface($node, $uses->getFQCN(), $this->file);
        } elseif ($node instanceof Function_) {
            $this->parseFunction($node);
        }
    }
    public function getResultScope() {
        return $this->fileScope;
    }

    /** @var FunctionTransformer */
    private $functionTransformer;
    /** @var FileScope */
    private $fileScope;
    private $file;
    private $parser;
    /** @var ClassParser */
    private $classParser;
    /** @var InterfaceParser */
    private $interfaceParser;
    /** @var UseParser */
    private $useParser;
    /** @var NamespaceParser */
    private $namespaceParser;
}
