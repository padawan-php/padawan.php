<?php

namespace Padawan\Framework\Complete\Resolver;

use Padawan\Domain\Event\TypeResolveEvent;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\FQN;
use Padawan\Domain\Scope;
use Padawan\Parser\UseParser;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\ShellExec;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Line;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Psr\Log\LoggerInterface;
use Padawan\Domain\Project\Chain;
use Padawan\Domain\Project\Chain\MethodCall as ChainMethodCall;
use Symfony\Component\EventDispatcher\EventDispatcher;

class NodeTypeResolver
{

    const BLOCK_START = 'type.block.before';
    const BLOCK_END = 'type.block.after';
    const TYPE_RESOLVED = 'type.resolving.after';

    public function __construct(
        LoggerInterface $logger,
        UseParser $useParser,
        EventDispatcher $dispatcher
    ) {
        $this->logger = $logger;
        $this->useParser = $useParser;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Calculates type of the passed node
     *
     * @param \PhpParser\Node\Expr $node
     * @param Index $index
     * @param Scope $scope
     * @return FQCN|null
     */
    public function getType($node, Index $index, Scope $scope)
    {
        if ($node instanceof Variable
            || $node instanceof PropertyFetch
            || $node instanceof StaticPropertyFetch
            || $node instanceof FuncCall
            || $node instanceof MethodCall
            || $node instanceof StaticCall
        ) {
            return $this->getLastChainNodeType($node, $index, $scope);
        }
        if ($node instanceof New_ && $node->class instanceof Name) {
            return $this->useParser->getFQCN($node->class);
        }
        if ($node instanceof Closure) {
            return new FQCN('Closure');
        }
        if ($node instanceof Cast\Bool_
            || $node instanceof BooleanNot
            || $node instanceof Isset_
            || $node instanceof Empty_
            || ($node instanceof ConstFetch && in_array(strtolower($node->name), ['true', 'false']))
        ) {
            return new FQCN('bool');
        }
        if ($node instanceof Cast\Array_
            || $node instanceof Array_
        ) {
            return new FQCN('array');
        }
        if ($node instanceof Cast\Object_) {
            return new FQCN('object');
        }
        if ($node instanceof Cast\Double
            || $node instanceof DNumber
        ) {
            return new FQCN('float');
        }
        if ($node instanceof Cast\Int_
            || $node instanceof LNumber
            || $node instanceof Line
            || $node instanceof Print_
        ) {
            return new FQCN('int');
        }
        if ($node instanceof Cast\String_
            || $node instanceof String_
            || $node instanceof Encapsed
            || $node instanceof MagicConst
            || $node instanceof ShellExec
        ) {
            return new FQCN('string');
        }
        return null;
    }

    /**
     * Calculates type of the passed last node in chain
     *
     * @param \PhpParser\Node $node
     * @param Index $index
     * @param Scope $scope
     * @return FQCN|null
     */
    public function getLastChainNodeType($node, Index $index, Scope $scope)
    {
        $types = $this->getChainType($node, $index, $scope);
        return array_pop($types);
    }

    /**
     * Calculates type of the passed chained node
     *
     * @param \PhpParser\Node $node
     * @param Index $index
     * @param Scope $scope
     * @return FQCN[]
     */
    public function getChainType($node, Index $index, Scope $scope)
    {
        /** @var FQCN */
        $type = null;
        $types = [];
        $chain = $this->createChain($node);
        $block = $chain;
        while ($block instanceof Chain) {
            if (is_string($block->getName())) {
                $this->logger->debug('looking for type of ' . $block->getName());
            }
            $event = new TypeResolveEvent($block, $type);
            $this->dispatcher->dispatch(self::BLOCK_START, $event);
            if ($block->getType() === 'var') {
                $type = $this->getVarType($block->getName(), $scope);
            } elseif ($block->getType() === 'method') {
                if (!($type instanceof FQN)) {
                    $types[] = null;
                    break;
                }
                $type = $this->getMethodType($block->getName(), $type, $index);
            } elseif ($block->getType() === 'property') {
                if (
                    !($type instanceof FQN)
                    || !is_string($block->getName())
                ) {
                    $types[] = null;
                    break;
                }
                $type = $this->getPropertyType($block->getName(), $type, $index);
            } elseif ($block->getType() === 'class') {
                $type = $block->getName();
                if ($type instanceof FQCN) {
                    if ($type instanceof FQCN && (
                        $type->getClassName() === 'self'
                        || $type->getClassName() === 'static'
                    )
                    ) {
                        $type = $scope->getFQCN();
                    } elseif ($type->getClassName() === 'parent'
                        && $scope->getFQCN() instanceof FQCN
                    ) {
                        $type = $this->getParentType($scope->getFQCN(), $index);
                    }
                }
            } elseif ($block->getType() === 'function') {
                $name = $block->getName();
                if ($name instanceof Name) {
                    $name = $name->toString();
                } elseif ($name instanceof Variable) {
                    $name = $name->name;
                }
                $function = $index->findFunctionByName($name);
                if (empty($function)) {
                    $type = null;
                } else {
                    $type = $function->return;
                }
            }
            $event = new TypeResolveEvent($block, $type);
            $this->dispatcher->dispatch(self::BLOCK_END, $event);
            $type = $event->getType();
            $block = $block->getChild();
            $types[] = $type;
        }
        $event = new TypeResolveEvent($chain, $type);
        $this->dispatcher->dispatch(self::TYPE_RESOLVED, $event);
        return $types;
    }

    /**
     * @return Chain
     */
    protected function createChain($node)
    {
        $chain = null;
        if ($node instanceof Assign) {
            $node = $node->expr;
        }
        while (!($node instanceof Variable) && !($node instanceof FuncCall)) {
            if ($node instanceof PropertyFetch
                || $node instanceof StaticPropertyFetch
            ) {
                $chain = new Chain($chain, $node->name, 'property');
            } elseif ($node instanceof MethodCall
                || $node instanceof StaticCall
            ) {
                $chain = new ChainMethodCall($chain, $node->name, $node->args);
            }
            if (empty($node) || !property_exists($node, 'var')) {
                break;
            }
            $node = $node->var;
        }
        if (!empty($node) && property_exists($node, 'class')) {
            $node = $node->class;
        }
        if ($node instanceof Variable) {
            $chain = new Chain($chain, $node->name, 'var');
        } elseif ($node instanceof Name) {
            $chain = new Chain($chain, $this->useParser->getFQCN($node), 'class');
        } elseif ($node instanceof FuncCall) {
            $chain = new Chain($chain, $node->name, 'function');
        }
        return $chain;
    }

    /**
     * @param string $name
     */
    protected function getVarType($name, Scope $scope)
    {
        $var = $scope->getVar($name);
        if (empty($var)) {
            return null;
        }
        return $var->getType();
    }

    /**
     * @param string $name
     */
    protected function getMethodType($name, FQCN $type, Index $index)
    {
        $class = $index->findClassByFQCN($type);
        if (empty($class)) {
            $class = $index->findInterfaceByFQCN($type);
        }
        if (empty($class)) {
            return null;
        }
        $method = $class->methods->get($name);
        if (empty($method)) {
            return null;
        }
        return $method->getReturn();
    }

    /**
     * @param string $name
     */
    protected function getPropertyType($name, FQCN $type, Index $index)
    {
        $class = $index->findClassByFQCN($type);
        if (empty($class)) {
            return null;
        }
        $prop = $class->properties->get($name);
        if (empty($prop)) {
            return null;
        }
        return $prop->getType();
    }
    protected function getParentType(FQCN $type, Index $index)
    {
        $class = $index->findClassByFQCN($type);
        if (empty($class)) {
            return null;
        }
        $parent = $class->getParent();
        if (empty($parent)) {
            return null;
        }
        return $parent->fqcn;
    }

    /** @property LoggerInterface */
    private $logger;
    /** @property UseParser */
    private $useParser;
    /** @var EventDispatcher */
    private $dispatcher;
}
