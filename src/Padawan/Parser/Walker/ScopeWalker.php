<?php

namespace Padawan\Parser\Walker;

use Padawan\Parser\UseParser;
use Padawan\Parser\CommentParser;
use Padawan\Parser\ParamParser;
use Padawan\Framework\Complete\Resolver\NodeTypeResolver;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Scope;
use Padawan\Domain\Scope\FileScope;
use Padawan\Domain\Scope\FunctionScope;
use Padawan\Domain\Scope\MethodScope;
use Padawan\Domain\Scope\ClassScope;
use Padawan\Domain\Scope\ClosureScope;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable as NodeVar;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\Closure;

class ScopeWalker extends NodeVisitorAbstract implements WalkerInterface
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
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof Class_) {
            $this->createScopeFromClass($node);
        } elseif ($node instanceof ClassMethod) {
            $this->createScopeFromMethod($node);
        } elseif ($node instanceof Closure) {
            $this->createScopeFromClosure($node);
        } elseif ($node instanceof Assign
            || $node instanceof Catch_
            || $node instanceof NodeVar
        ) {
            $this->addVarToScope($node);
        }
    }
    public function leaveNode(Node $node)
    {
        if (!$this->isIn($node, $this->line)) {
        }
    }
    public function updateFileInfo(Uses $uses, $file)
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
        $classData = null;
        if (!$node->static && $scope instanceof MethodScope) {
            $index = $this->getIndex();
            $classData = $index->findClassByFQCN($scope->getClass()->fqcn);
        }
        $this->scope = new ClosureScope($scope, $classData);
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
    public function addVarToScope($node)
    {
        if ($node instanceof Assign) {
            if (!$node->var instanceof NodeVar) {
                return;
            }
            $var = new Variable($node->var->name);
        } elseif ($node instanceof Catch_) {
            $var = new Variable($node->var);
        } elseif ($node instanceof NodeVar) {
            $var = new Variable($node->name);
        }

        $comment = $this->commentParser->parse($node->getAttribute('comments'));
        if ($node instanceof Catch_ && count($node->types) === 1) {
            $type = $this->useParser->getFQCN($node->types[0]);
        } elseif ($comment->getVar($var->getName())) {
            $type = $comment->getVar($var->getName())->getType();
        } elseif (isset($node->expr)) {
            $type = $this->typeResolver->getType(
                $node->expr,
                $this->getIndex(),
                $this->scope
            );
        }

        $current = $this->scope->getVar($var->getName());
        if (!isset($type) && $current && $current->getType()) {
            return;
        }

        if (isset($type)) {
            $var->setType($type);
        }

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
