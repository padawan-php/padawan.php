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

class NodeTypeResolver {

    public function __construct(
        LoggerInterface $logger,
        UseParser $useParser
    ){
        $this->logger = $logger;
        $this->useParser = $useParser;
    }

    /**
     * Calculates type of the passed node
     *
     * @param PhpParser\Node $node
     * @param Index $index
     * @param Scope $scope
     * @return FQCN|null
     */
    public function getType($node, Index $index, Scope $scope){
        if($node instanceof Variable
            || $node instanceof PropertyFetch
            || $node instanceof StaticPropertyFetch
            || $node instanceof MethodCall
            || $node instanceof StaticCall
        ){
            return $this->getChainType($node, $index, $scope);
        }
        elseif($node instanceof New_) {
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
    public function getChainType($node, Index $index, Scope $scope){
        /** @var FQCN */
        $type = null;
        $chain = $this->createChain($node);
        foreach($chain AS $block){
            $this->logger->addDebug('looking for type of ' . $block['name']);
            if($block['type'] === 'var'){
                $type = $this->getVarType($block['name'], $scope);
            }
            elseif($block['type'] === 'method'){
                if(!($type instanceof FQN)){
                    return null;
                }
                $type = $this->getMethodType($block['name'], $type, $index);
            }
            elseif($block['type'] === 'property'){
                if(!($type instanceof FQN)){
                    return null;
                }
                $type = $this->getPropertyType($block['name'], $type, $index);
            }
            elseif($block['type'] === 'class'){
                $type = $block['name'];
                if(
                    $type->getClassName() === 'self'
                    || $type->getClassName() === 'static'
                ){
                    $type = $scope->getFQCN();
                }
                elseif($type->getClassName() === 'parent'){
                    $type = $this->getParentType($scope->getFQCN(), $index);
                }
            }
        }
        return $type;
    }
    protected function createChain($node){
        $chain = [];
        while(!($node instanceof Variable)){
            if(
                $node instanceof PropertyFetch
                || $node instanceof StaticPropertyFetch
            ){
                $chain[] = [
                    'type' => 'property',
                    'name' => $node->name
                ];
            }
            elseif(
                $node instanceof MethodCall
                || $node instanceof StaticCall
            ){
                $chain[] = [
                    'type' => 'method',
                    'name' => $node->name
                ];
            }
            if(empty($node) || !property_exists($node, 'var')){
                break;
            }
            $node = $node->var;
        }
        if(property_exists($node, 'class')){
            $node = $node->class;
        }
        if($node instanceof Variable){
            $chain[] = [
                'type' => 'var',
                'name' => $node->name
            ];
        }
        elseif($node instanceof Name){
            $chain[] = [
                'type' => 'class',
                'name' => $this->useParser->getFQCN($node)
            ];
        }
        return array_reverse($chain);
    }
    protected function getVarType($name, Scope $scope){
        $var = $scope->getVar($name);
        if(empty($var)){
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
}
