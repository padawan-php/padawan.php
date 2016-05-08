<?php

namespace Padawan\Parser\Walker;

use Padawan\Domain\Scope\FileScope;
use Padawan\Domain\Scope\ClassScope;
use Padawan\Domain\Scope\FunctionScope;
use Padawan\Domain\Scope\MethodScope;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\FQN;
use Padawan\Domain\Project\Index;
use Padawan\Parser\Transformer\FunctionTransformer;
use Padawan\Parser\Transformer\ClassAssignmentTransformer;
use Padawan\Parser\ClassParser;
use Padawan\Parser\InterfaceParser;
use Padawan\Parser\UseParser;
use Padawan\Parser\NamespaceParser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\NodeTraverser;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Expr\Assign;

class IndexGeneratingWalker extends NodeVisitorAbstract implements WalkerInterface
{
    public function __construct(
        ClassParser $classParser,
        InterfaceParser $interfaceParser,
        UseParser $useParser,
        NamespaceParser $namespaceParser,
        FunctionTransformer $functionTransformer,
        ClassAssignmentTransformer $constructorAssignments
    ) {
        $this->classTransformer = $classParser;
        $this->interfaceTransformer = $interfaceParser;
        $this->useTransformer = $useParser;
        $this->namespaceTransformer = $namespaceParser;
        $this->scope = new FileScope(new FQN);
        $this->fileScope = $this->scope;
        $this->functionTransformer = $functionTransformer;
        $this->constructorAssignments = $constructorAssignments;
    }
    public function updateFileInfo(Uses $uses, $file)
    {
        $this->file = $file;
        $this->scope = new FileScope($uses->getFQCN(), $uses);
        $this->fileScope = $this->scope;
    }
    public function setIndex(Index $index)
    {
        $this->index = $index;
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            $this->scope = new ClassScope(
                $this->scope,
                $this->classTransformer->parse($node, $this->scope->getNamespace(), $this->file)
            );
            return null;
        } elseif ($node instanceof Interface_) {
            $this->scope = new ClassScope(
                $this->scope,
                $this->interfaceTransformer->parse($node, $this->scope->getNamespace(), $this->file)
            );
            return null;
        } elseif ($node instanceof Function_) {
            $this->scope = new FunctionScope(
                $this->scope,
                $this->functionTransformer->tranform($node)
            );
            return null;
        } elseif ($node instanceof ClassMethod) {
            $this->scope = new MethodScope(
                $this->scope,
                $this->scope->getClass()->getMethod($node->name)
            );
            if ($node->name === '__construct') {
                return null;
            }
        } elseif ($node instanceof Namespace_) {
            $this->namespaceTransformer->parse($node);
            return null;
        } elseif ($node instanceof Use_) {
            $this->useTransformer->parse($node);
            return null;
        }
        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_) {
            $this->scope = $this->scope->getParent();
        } elseif ($node instanceof Interface_) {
            $this->scope = $this->scope->getParent();
        } elseif ($node instanceof Function_) {
            $this->scope = $this->scope->getParent();
        } elseif ($node instanceof ClassMethod) {
            $this->scope = $this->scope->getParent();
        } elseif ($node instanceof Assign && $this->scope instanceof MethodScope) {
            $this->constructorAssignments->transform(
                $node,
                $this->scope->getClass(),
                $this->scope,
                $this->index
            );
        }
    }
    public function getResultScope()
    {
        return $this->fileScope;
    }

    private $scope;
    private $fileScope;
    private $file;
    private $classTransformer;
    private $interfaceTransformer;
    private $functionTransformer;
    private $useTransformer;
    private $namespaceTransformer;
    private $constructorAssignments;
    /** @var Index */
    private $index;
}
