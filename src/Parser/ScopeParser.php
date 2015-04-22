<?php

namespace Parser;

use Entity\FQCN;
use Entity\Project;
use Entity\Node\Variable;
use Entity\Node\Uses;
use Entity\Completion\Scope;
use PhpParser\Parser AS ASTGenerator;
use PhpParser\NodeTraverser AS Traverser;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Expr\Assign;

class ScopeParser{
    /** @var PhpParser */
    private $parser;
    /** @var UseParser */
    private $useParser;
    /** @var Scope */
    private $scope;
    /** @var Project */
    private $project;

    public function __construct(
        ASTGenerator $parser,
        UseParser $useParser
    ){
        $this->parser           = $parser;
        $this->useParser        = $useParser;
    }
    public function parseContent(Project $project, Scope $scope, $content, $line){
        $this->scope = $scope;
        $this->project = $project;
        $fqcn = $scope->getFQCN();
        try{
            $uses = new Uses($this->parseFQCN($fqcn->getNamespace()));
            $this->useParser->setUses($uses);
            $ast = $this->parser->parse($content);
            $this->traverse($ast, $line);
        }
        catch(\Exception $e){
            printf("Parsing failed in fqcn %s\n", $fqcn->toString());
            printf("Error: %s\n", $e->getMessage());
        }
        return $this->scope;
    }
    public function traverse($stmts, $line){
        if(!is_array($stmts)){
            return;
        }
        foreach($stmts AS $node){
            if($this->isIn($node, $line)){
                //$this->lookNode($node, $line);
            }
        }
    }
    public function lookNode($node, $line){
        list($startLine, $endLine) = $this->getNodeLines($node);
        if($node instanceof ClassMethod){
            $this->createScopeFromMethod($node);
        }
        elseif($node instanceof Assign){
            $this->addVarToScope($node);
        }
        if(in_array('stmts', $node->getSubNodeNames()))
            $this->traverse($node->stmts, $line);
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
        $this->scope = new Scope($this->scope);
        $index = $this->project->getIndex();
        $fqcn = $this->scope->getFQCN();
        $classData = $index->findClassByFQCN($fqcn);
        if(empty($classData)){
            printf("Empty class in %s", $fqcn->toString());
            return;
        }
        $method = $classData->methods->get($node->name);
        if(empty($method)){
            printf("Empty method in %s::%s", $fqcn->toString(), $node->name);
            return;
        }
        foreach($method->arguments AS $param){
            $var = new Variable($param->name);
            $var->setFQCN($param->type);
            $this->scope->addVar($var);
        }
    }
    public function addVarToScope(Assign $node){
    }
    public function parseUse(Use_ $node, $fqcn, $file){
        $this->useParser->parse($node, $fqcn, $file);
    }
    public function parseFQCN($fqcn){
        return $this->useParser->parseFQCN($fqcn);
    }
}
