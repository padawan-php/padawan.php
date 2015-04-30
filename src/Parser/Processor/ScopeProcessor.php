<?php

namespace Parser\Processor;

use Parser\UseParser;
use Entity\FQCN;
use Entity\Index;
use Entity\Node\Variable;
use Entity\Completion\Scope;
use Complete\Resolver\NodeTypeResolver;
use PhpParser\NodeTraverserInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\Assign;
use PhpParser\NodeVisitorAbstract;

class ScopeProcessor extends NodeVisitorAbstract implements ProcessorInterface {
    public function __construct(
        UseParser $useParser,
        NodeTypeResolver $typeResolver
    ){
        $this->resultNodes      = [];
        $this->useParser        = $useParser;
        $this->typeResolver     = $typeResolver;
    }
    public function setLine($line){
        $this->line = $line;
    }
    public function enterNode(Node $node){
        list($startLine, $endLine) = $this->getNodeLines($node);
        if(!$this->isIn($node, $this->line)){
            return NodeTraverserInterface::DONT_TRAVERSE_CHILDREN;
        }
        if($node instanceof Class_){
            $this->scope = new Scope($this->scope);
            $var = new Variable('this');
            $var->setType($this->scope->getFQCN());
            $this->scope->addVar($var);
        }
        elseif($node instanceof ClassMethod){
            $this->createScopeFromMethod($node);
        }
        elseif($node instanceof Assign){
            $this->addVarToScope($node);
        }
    }
    public function leaveNode(Node $node){
        if(!$this->isIn($node, $this->line)){
            //if($node instanceof Class_ || $node instanceof ClassMethod){
                //if(!empty($this->scope) && !empty($this->scope->getParent())){
                    //$this->scope = $this->scope->getParent();
                //}
            //}
        }
    }
    public function setFileInfo(FQCN $fqcn, $file){
        $this->scope = new Scope($this->scope);
        $this->scope->setFQCN($fqcn);
    }
    public function clearResultNodes(){
        $this->resultNodes = [];
    }
    public function getResultNodes(){
        $this->resultNodes = [ $this->scope ];
        return $this->resultNodes;
    }
    public function isIn($node, $line){
        list($startLine, $endLine) = $this->getNodeLines($node);
        if($node instanceof ClassMethod){
            return $line >= $startLine && $line <= $endLine;
        }
        return $line >= $startLine;
    }
    public function getNodeLines($node){
        $startLine = $endLine = -1;
        if($node->hasAttribute('startLine')){
            $startLine = $node->getAttribute('startLine');
        }
        if($node->hasAttribute('endLine')){
            $endLine = $node->getAttribute('endLine');
        }
        return [$startLine, $endLine];
    }
    public function createScopeFromMethod(ClassMethod $node){
        $index = $this->getIndex();
        if(empty($index)){
            echo "empty index\n";
            return;
        }
        $fqcn = $this->scope->getFQCN();
        $classData = $index->findClassByFQCN($fqcn);
        if(empty($classData)){
            printf("Empty class in %s\n", $fqcn->toString());
            return;
        }
        $method = $classData->methods->get($node->name);
        if(empty($method)){
            printf("Empty method in %s::%s\n", $fqcn->toString(), $node->name);
            return;
        }
        foreach($method->arguments AS $param){
            $this->scope->addVar($param);
        }
    }
    public function addVarToScope(Assign $node){
        $var = new Variable($node->var->name);
        $var->setType($this->typeResolver->getType(
            $node->expr, $this->getIndex(), $this->scope
        ));
        $this->scope->addVar($var);
    }
    public function parseUse(Use_ $node, $fqcn, $file){
        $this->useParser->parse($node, $fqcn, $file);
    }

    /**
     * @return FQCN
     */
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }

    /**
     * @return Index
     */
    public function getIndex(){
        return $this->index;
    }
    public function setIndex(Index $index){
        $this->index = $index;
    }

    /** @var ClassData[]|InterfaceData[] */
    private $resultNodes;
    private $line;
    /** @var UseParser */
    private $useParser;
    private $index;
    /** @property Scope */
    private $scope;
    private $typeResolver;
}
