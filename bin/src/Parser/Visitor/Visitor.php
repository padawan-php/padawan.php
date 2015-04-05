<?php

namespace Parser\Visitor;

use \Entity\FQCN;
use \PhpParser\Node;
use \PhpParser\NodeVisitorAbstract;
use \PhpParser\Node\Stmt\Class_;
use \PhpParser\Node\Stmt\Interface_;
use \PhpParser\Node\Stmt\Use_;

class Visitor extends NodeVisitorAbstract{
    private $fqcn;
    private $file;
    private $parser;
    public function setFileInfo(FQCN $fqcn, $file){
        $this->fqcn = $fqcn;
        $this->file = $file;
    }
    public function setParser($parser){
        $this->parser = $parser;
    }
    public function enterNode(Node $node){
        if($node instanceof Use_){
            $this->parser->parseUse($node, $this->fqcn, $this->file);
        }
    }
    public function leaveNode(Node $node){
        if($node instanceof Class_){
            $this->parser->parseClass($node, $this->fqcn, $this->file);
        }
        elseif($node instanceof Interface_){
            $this->parser->parseInterface($node, $this->fqcn, $this->file);
        }
    }
}
