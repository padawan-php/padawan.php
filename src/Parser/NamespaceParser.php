<?php

namespace Parser;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Name;
use Entity\FQN;
use Entity\Node\Uses;

class NamespaceParser {
    public function parse(Namespace_ $node){
        if($node->name instanceof Name){
            $fqn = new FQN($node->name->parts);
            $this->uses->setFQCN($fqn);
        }
    }
    public function setUses(Uses $uses = null){
        $this->uses = $uses;
    }
    private $uses;
}
