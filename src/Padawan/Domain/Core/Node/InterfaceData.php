<?php

namespace Padawan\Domain\Core\Node;

use Padawan\Domain\Core\FQCN;
use Padawan\Domain\Core\Collection\MethodsCollection;

class InterfaceData
{
    public $fqcn;
    public $interfaces      = [];
    public $constants       = [];
    public $uses            = [];
    public $methods;
    public $file            = "";
    public $startLine       = 0;
    public $doc             = "";
    public function __construct(FQCN $fqcn, $file) {
        $this->fqcn = $fqcn;
        $this->file = $file;
        $this->methods = new MethodsCollection($this);
    }
    /** @return FQCN */
    public function getFQCN()
    {
        return $this->fqcn;
    }
    public function getName()
    {
        return $this->getFQCN()->getClassName();
    }
    public function addMethod(MethodData $method) {
        $this->methods->add($method);
    }
    public function getMethod($methodName)
    {
        return $this->methods->get($methodName);
    }
    public function hasMethod($methodName)
    {
        return $this->methods->get($methodName) !== null;
    }
    public function getInterfaces() {
        return $this->interfaces;
    }
    public function addInterface($interface) {
        $fqcn = $interface;
        if ($interface instanceof InterfaceData) {
            $fqcn = $interface->fqcn;
        }
        $this->interfaces[$fqcn->toString()] = $interface;
    }
}
