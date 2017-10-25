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
use Padawan\Domain\Scope\AbstractChildScope;
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
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
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
        } elseif ($node instanceof Function_) {
            $this->createScopeFromFunction($node);
        } elseif ($node instanceof Closure) {
            $this->createScopeFromClosure($node);
        } elseif ($node instanceof Assign
            || $node instanceof StaticVar
            || $node instanceof Catch_
            || $node instanceof Foreach_
            || $node instanceof Global_
            || $node instanceof NodeVar
        ) {
            $this->addVarToScope($node);
        } elseif ($node instanceof Unset_) {
            $this->removeVarFromScope($node);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
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
        if ($node instanceof Class_
            || $node instanceof ClassMethod
            || $node instanceof Function_
            || $node instanceof Closure
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
    public function createScopeFromFunction(Function_ $node)
    {
        $scope = $this->scope;
        $index = $this->getIndex();
        if (empty($index)) {
            return;
        }
        $function = $index->findFunctionByName($node->name);
        if (empty($function)) {
            return;
        }
        $this->scope = new FunctionScope($scope, $function);
    }
    /**
     * @param Global_|Assign|Catch_|Foreach_|StaticVar|NodeVar $node
     */
    public function addVarToScope($node)
    {
        if ($node instanceof Global_) {
            $parent = $this->scope;
            while ($parent instanceof AbstractChildScope) {
                $parent = $parent->getParent();
            }
            foreach ($node->vars as $nodeVar) {
                $var = $parent->getVar($nodeVar->name);
                if ($var) {
                    $this->scope->addVar($var);
                }
            }
            return;
        }

        if ($node instanceof Assign
            || $node instanceof StaticVar
            || $node instanceof Foreach_
        ) {
            if (isset($node->var)) {
                $nodeVar = $node->var;
            } elseif (isset($node->valueVar)) {
                $nodeVar = $node->valueVar;
            } elseif (isset($node->name)) {
                $nodeVar = new NodeVar($node->name);
            }

            if ($nodeVar instanceof List_ || $nodeVar instanceof Array_) {
                return $this->addListToScope($nodeVar, $node);
            }
            if (!isset($nodeVar->name)) {
                return;
            }

            $var = new Variable($nodeVar->name);
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
        } elseif (isset($node->expr) || isset($node->default)) {
            $expr = isset($node->expr) ? $node->expr : $node->default;
            $type = $this->typeResolver->getType(
                $expr,
                $this->getIndex(),
                $this->scope
            );
        }

        $current = $this->scope->getVar($var->getName());
        if (!isset($type) && $current && $current->getType()) {
            return;
        }

        if ($node instanceof Foreach_) {
            if (!isset($type) || !$type instanceof FQCN || !$type->isArray()) {
                return;
            }
            $type = new FQCN($type->className, $type->namespace, $type->getDimension() - 1);
        }

        if (isset($type)) {
            $var->setType($type);
        }

        $this->scope->addVar($var);
    }
    /**
     * @param List_|Array_    $list
     * @param Assign|Foreach_ $parent
     */
    public function addListToScope($list, $parent, $level = 1)
    {
        $comment = $this->commentParser->parse($parent->getAttribute('comments'));
        if ($parent->expr instanceof NodeVar && $comment->getVar($parent->expr->name)) {
            $type = $comment->getVar($parent->expr->name)->getType();
        } else {
            $type = $this->typeResolver->getType(
                $parent->expr,
                $this->getIndex(),
                $this->scope
            );
        }

        if (!isset($type) || !$type instanceof FQCN || !$type->isArray()) {
            return;
        }

        foreach ($list->items as $item) {
            if (!isset($item->value)) {
                continue;
            }
            if ($item->value instanceof List_ || $item->value instanceof Array_) {
                $this->addListToScope($item->value, $parent, $level + 1);
                continue;
            }
            if ($item->value instanceof NodeVar) {
                $var = new Variable($item->value->name);
                $var->setType(new FQCN($type->className, $type->namespace, $type->getDimension() - $level));
                $this->scope->addVar($var);
            }
        }
    }
    public function removeVarFromScope(Unset_ $node)
    {
        foreach ($node->vars as $expr) {
            if ($expr instanceof NodeVar) {
                $var = $this->scope->getVar($expr->name);
                if ($var) {
                    $this->scope->removeVar($var);
                }
            }
        }
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
