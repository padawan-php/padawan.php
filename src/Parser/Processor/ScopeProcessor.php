<?php

namespace Parser\Processor;

use PhpParser\NodeVisitorAbstract;
use Entity\FQCN;

class ScopeProcessor extends NodeVisitorAbstract implements ProcessorInterface {
    public function setFileInfo(FQCN $fqcn, $file){

    }
    public function clearResultNodes(){
        $this->resultNodes = [];
    }
    public function getResultNodes(){
        return $this->resultNodes;
    }
    protected function addResultNode($resultNode){
        if(!$resultNode){
            return;
        }
        $this->resultNodes[] = $resultNode;
    }
    /** @var ClassData[]|InterfaceData[] */
    private $resultNodes;
}
