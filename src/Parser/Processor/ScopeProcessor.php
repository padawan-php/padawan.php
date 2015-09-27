<?php

namespace Parser\Processor;

use Parser\UseParser;
use Parser\CommentParser;
use Parser\ParamParser;
use Complete\Resolver\NodeTypeResolver;
use Entity\FQCN;
use Entity\Index;
use Entity\Node\Uses;
use Entity\Node\Variable;
use Entity\Completion\Scope;
use Entity\Completion\Scope\FileScope;
use Entity\Completion\Scope\FunctionScope;
use Entity\Completion\Scope\MethodScope;
use Entity\Completion\Scope\ClassScope;
use Entity\Completion\Scope\ClosureScope;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable as NodeVar;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\Closure;

class ScopeProcessor extends NodeVisitorAbstract implements ProcessorInterface
{
    public function __construct(
        UseParser $useParser,
        NodeTypeResolver $typeResolver,
        CommentParser $commentParser,
        ParamParser $paramParser
    ) {
        $this->useParser        = $useParser;
        $this->typeResolver     = $typeResolver;
        $this->commentParser    = $commentParser;
        $this->paramParser      = $paramParser;
    }
    public function setLine($line)
    {
        $this->line = $line;
    }
    public function enterNode(Node $node)
    {
        list($startLine, $endLine) = $this->getNodeLines($node);
        if (!$this->isIn($node, $this->line)) {
            return NodeTraverserInterface::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof Class_) {
            $this->createScopeFromClass($node);
        } elseif ($node instanceof ClassMethod) {
            $this->createScopeFromMethod($node);
        } elseif ($node instanceof Closure) {
            $this->createScopeFromClosure($node);
        } elseif ($node instanceof Assign) {
            $this->addVarToScope($node);
        }
    }
    public function leaveNode(Node $node)
    {
        if (!$this->isIn($node, $this->line)) {
        }
    }
    public function setFileInfo(Uses $uses, $file)
    {
        $this->scope = new FileScope($uses->getFQCN(), $uses);
        $this->fileScope = $this->scope;
    }
    public function getResultScope()
    {
        return $this->scope;
    }

    /**
     * @param Node $node
     */
    public function isIn($node, $line)
    {
        list($startLine, $endLine) = $this->getNodeLines($node);
        if ($node instanceof ClassMethod
            || $node instanceof Closure
            || $node instanceof Class_
        ) {
            return $line >= $startLine && $line <= $endLine;
        }
        return $line >= $startLine;
    }
    public function getNodeLines($node)
    {
        $startLine = $endLine = -1;
        if ($node->hasAttribute('startLine')) {
            $startLine = $node->getAttribute('startLine');
        }
        if ($node->hasAttribute('endLine')) {
            $endLine = $node->getAttribute('endLine');
        }
        return [$startLine, $endLine];
    }
    protected function createScopeFromClass(Class_ $node)
    {
        $scope = $this->scope;
        $index = $this->getIndex();
        if (empty($index)) {
            return;
        }
        $fqcn = new FQCN(
            $node->name,
            $this->scope->getNamespace()
        );
        $classData = $index->findClassByFQCN($fqcn);
        if (empty($classData)) {
            return;
        }
        $this->scope = new ClassScope($scope, $classData);
    }
    public function createScopeFromClosure(Closure $node)
    {
        $scope = $this->scope;
        $this->scope = new ClosureScope($scope);
        foreach ($node->params as $param) {
            $this->scope->addVar(
                $this->paramParser->parse($param)
            );
        }
        foreach ($node->uses as $closureUse) {
            $var = $this->scope->getParent()->getVar($closureUse->var);
            if ($var instanceof Variable) {
                $this->scope->addVar(
                    $var
                );
            }
        }
    }
    public function createScopeFromMethod(ClassMethod $node)
    {
        $classScope = $this->scope;
        $index = $this->getIndex();
        if (empty($index)) {
            return;
        }
        $fqcn = $classScope->getFQCN();
        $classData = $index->findClassByFQCN($fqcn);
        if (empty($classData)) {
            return;
        }
        $method = $classData->methods->get($node->name);
        if (empty($method)) {
            return;
        }
        $this->scope = new MethodScope($classScope, $method);
    }
    public function addVarToScope(Assign $node)
    {
        if (!$node->var instanceof NodeVar) {
            return;
        }
        $var = new Variable($node->var->name);
        $comment = $this->commentParser->parse($node->getAttribute('comments'));
        if ($comment->getVar($var->getName())) {
            $type = $comment->getVar($var->getName())->getType();
        } else {
            $type = $this->typeResolver->getType(
                $node->expr,
                $this->getIndex(),
                $this->scope
            );
        }
        $var->setType($type);
        $this->scope->addVar($var);
    }
    public function parseUse(Use_ $node, $fqcn, $file)
    {
        $this->useParser->parse($node, $fqcn, $file);
    }

    /**
     * @return FQCN
     */
    public function parseFQCN($fqcn)
    {
        return $this->useParser->parseFQCN($fqcn);
    }

    /**
     * @return Index
     */
    public function getIndex()
    {
        return $this->index;
    }
    public function setIndex(Index $index)
    {
        $this->index = $index;
    }

    /** @var FileScope */
    private $fileScope;
    private $line;
    /** @var UseParser */
    private $useParser;
    private $index;
    /** @property Scope */
    private $scope;
    private $typeResolver;
    private $commentParser;
    /** @property ParamParser */
    private $paramParser;
}
