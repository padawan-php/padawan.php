<?php

namespace Parser\Visitor;

use \PhpParser\Node;
use \DTO\ClassData;
use \PhpParser\NodeVisitorAbstract;
use \PhpParser\Node\Stmt\Class_;

class Visitor extends NodeVisitorAbstract{
    private $classData;
    private $parser;
    public function setClassData(ClassData $classData){
        $this->classData = $classData;
    }
    public function setParser($parser){
        $this->parser = $parser;
    }
    public function leaveNode(Node $node){
        if($node instanceof Class_){
            $classData = $this->classData;
            if($classData->fqcn->className != $node->name){
                printf("For FQCN %s in file %s found class %s\n",
                    $classData->fqcn->toString(),
                    $classData->file,
                    $node->name
                );
                return;
            }
            $this->parser->parseClass($node, $this->classData);
        }
    }
}
