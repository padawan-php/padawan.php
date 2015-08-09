<?php

namespace Complete\Resolver;

use Entity\Index;
use Entity\FQCN;
use Entity\FQN;
use Entity\Completion\Scope;
use Parser\UseParser;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Psr\Log\LoggerInterface;
use Entity\Chain;
use Entity\Chain\MethodCall as ChainMethodCall;
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
     * @param PhpParser\Node $node
     * @param Index $index
     * @param Scope $scope
     * @return FQCN|null
     */
    public function getType($node, Index $index, Scope $scope)
    {
        if ($node instanceof Variable
            || $node instanceof PropertyFetch
            || $node instanceof StaticPropertyFetch
            || $node instanceof MethodCall
            || $node instanceof StaticCall
        ) {
            return $this->getChainType($node, $index, $scope);
        } elseif ($node instanceof New_) {
            return $this->useParser->getFQCN($node->class);
        }
        return null;
    }

    /**
     * Calculates type of the passed chained node
     *
     * @param PhpParser\Node $node
     * @param Index $index
     * @param Scope $scope
     * @return FQCN|null
     */
    public function getChainType($node, Index $index, Scope $scope)
    {
        /** @var FQCN */
        $type = null;
        $chain = $this->createChain($node);
        $block = $chain;
        while ($block instanceof Chain) {
            $this->logger->addDebug('looking for type of ' . $block->getName());
            $event = new TypeResolveEvent($block, $type);
            $this->dispatcher->dispatch(self::BLOCK_START, $event);
            if ($block->getType() === 'var') {
                $type = $this->getVarType($block->getName(), $scope);
            } elseif ($block->getType() === 'method') {
                if (!($type instanceof FQN)) {
                    $type = null;
                    break;
                }
                $type = $this->getMethodType($block->getName(), $type, $index);
            } elseif ($block->getType() === 'property') {
                if (!($type instanceof FQN)) {
                    $type = null;
                    break;
                }
                $type = $this->getPropertyType($block->getName(), $type, $index);
            } elseif ($block->getType() === 'class') {
                $type = $block->getName();
                if ($type->getClassName() === 'self'
                    || $type->getClassName() === 'static'
                ) {
                    $type = $scope->getFQCN();
                } elseif ($type->getClassName() === 'parent') {
                    $type = $this->getParentType($scope->getFQCN(), $index);
                }
            }
            $event = new TypeResolveEvent($block, $type);
            $this->dispatcher->dispatch(self::BLOCK_END, $event);
            $type = $event->getType();
            $block = $block->getChild();
        }
        $event = new TypeResolveEvent($chain, $type);
        $this->dispatcher->dispatch(self::TYPE_RESOLVED, $event);
        return $type;
    }

    /**
     * @return Chain
     */
    protected function createChain($node)
    {
        $chain = null;
        while (!($node instanceof Variable)) {
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
        }
        return $chain;
    }
    protected function getVarType($name, Scope $scope)
    {
        $var = $scope->getVar($name);
        if (empty($var)) {
            return null;
        }
        return $var->getType();
    }
    protected function getMethodType($name, FQCN $type, Index $index){
        $class = $index->findClassByFQCN($type);
        if(empty($class)){
            $class = $index->findInterfaceByFQCN($type);
        }
        if(empty($class)){
            return null;
        }
        $method = $class->methods->get($name);
        if(empty($method)){
            return null;
        }
        return $method->getReturn();
    }
    protected function getPropertyType($name, FQCN $type, Index $index){
        $class = $index->findClassByFQCN($type);
        if(empty($class)){
            return null;
        }
        $prop = $class->properties->get($name);
        if(empty($prop)){
            return null;
        }
        return $prop->getType();
    }
    protected function getParentType(FQCN $type, Index $index){
        $class = $index->findClassByFQCN($type);
        if(empty($class)){
            return null;
        }
        $parent = $class->getParent();
        if(empty($parent)){
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
