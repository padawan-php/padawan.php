<?php

namespace Padawan\Parser;

use Padawan\Domain\Project\FQN;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Node\Uses;
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
        return $this->uses;
    }

    /**
     * @param string $type
     */
    public function parseType($type){
        $pureFQCN = $this->parseFQCN($type);
        if($pureFQCN->isScalar()){
            return $pureFQCN;
        }
        if(strpos($type, '\\') === 0){
            return $pureFQCN;
        }
        $fqcn = $this->uses->find($type);
        if(!empty($fqcn)){
            return $fqcn;
        }
        return $this->createFQCN($pureFQCN);
    }
    public function getFQCN(Name $node = null){
        if($node === null) {
            return $node;
        }
        if($node->isFullyQualified()){
            return $this->parseFQCN($node->toString());
        }
        $fqcn = $this->uses->find($node->getFirst());
        if($fqcn){
            if ($node->isUnqualified()) {
                return $fqcn;
            }
            return $fqcn->join(new FQN($node->slice(1)->toString()));
        }
        return $this->createFQCN($node->toString());
    }
    public function parseFQCN($fqcn){
        $fqcn = trim($fqcn, '\\');
        if(empty($fqcn)){
            return new FQCN('');
        }
        $parts = explode('\\', $fqcn);
        $name = array_pop($parts);
        $regex = '/(\w+)((?:\[\])*)/';
        preg_match($regex, $name, $matches);
        if(count($matches) === 0){
            throw new \Exception("Could not parse FQCN for empty class name: " . $fqcn);
        }
        $name = $matches[1];
        $dimension = isset($matches[2]) ? strlen($matches[2]) / 2 : 0;
        return new FQCN(
            $name,
            $parts,
            $dimension
        );
    }
    public function getUses() {
        return $this->uses;
    }
    public function setUses(Uses $uses = null) {
        $this->uses = $uses;
    }
    protected function createFQCN($fqcn) {
        $fqn = $this->uses->getFQCN()->join($this->parseFQCN($fqcn));
        return new FQCN($fqn->getLast(), $fqn->getTail());
    }
}
