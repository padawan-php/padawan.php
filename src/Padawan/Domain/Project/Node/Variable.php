<?php

namespace Padawan\Domain\Project\Node;

class Variable {
    public function __construct($name, $startLine = 0) {
        $this->name = $name;
        $this->startLine = $startLine;
    }
    public function getName() {
        return $this->name;
    }
    public function getFQCN() {
        return $this->fqcn;
    }
    public function setFQCN($fqcn) {
        $this->fqcn = $fqcn;
    }
    public function setType($fqcn) {
        $this->setFQCN($fqcn);
    }
    public function getType() {
        return $this->getFQCN();
    }
    public function getStartLine() {
        return $this->startLine;
    }
    public function setStartLine($startLine) {
        $this->startLine = $startLine;
    }
    private $name;
    private $fqcn;
    private $startLine;
}
