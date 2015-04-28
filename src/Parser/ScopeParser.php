<?php

namespace Parser;

use Entity\FQCN;
use Entity\Project;
use Entity\Node\Uses;
use Entity\Completion\Scope;
use PhpParser\Parser AS ASTGenerator;
use PhpParser\NodeTraverser AS Traverser;

class ScopeParser{
    /** @var PhpParser */
    private $parser;
    /** @var Scope */
    private $scope;
    /** @var Project */
    private $project;

    public function __construct(
        ASTGenerator $parser,
        UseParser $useParser
    ){
        $this->parser           = $parser;
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
    }
}
