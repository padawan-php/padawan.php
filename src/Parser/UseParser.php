<?php

namespace Parser;

use Entity\FQCN;
use Entity\Node\Uses;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;

class UseParser {
    /** @var Uses */
    private $uses;
    public function parse(Use_ $node){
        foreach($node->uses AS $use){
            $fqcn = $this->parseFQCN($use->name->toString());
            $this->uses->add($fqcn, $use->alias);
        }
    }
    public function getFQCN(Name $node = null){
        if($node === null)
            return $node;
        if($node->isFullyQualified()){
            return $this->parseFQCN($node->toString());
        }
        $fqcn = $this->uses->find($node->getFirst());
        if($fqcn){
            return $fqcn;
        }
        $fqcn = $this->uses->getFQCN()->join($this->parseFQCN($node->toString()));
        return $fqcn;
    }
    public function parseFQCN($fqcn){
        $regex = '/(.*)(?=\\\\(\w+)$)|(.*)/';
        $ret = preg_match($regex, $fqcn, $matches);
        if(!$ret) {
            throw new \Exception("Error while parsing FQCN");
        }
        return new FQCN(
            count($matches) == 3 ? $matches[2] : $matches[3],
            count($matches) == 3 ? $matches[1] : ""
        );
    }
    public function getUses(){
        return $this->uses;
    }
    public function setUses(Uses $uses = null){
        $this->uses = $uses;
    }
}
