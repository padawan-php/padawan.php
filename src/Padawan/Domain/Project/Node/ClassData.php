<?php

namespace Padawan\Domain\Project\Node;

use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\FQN;
use Padawan\Domain\Project\Collection\MethodsCollection;
use Padawan\Domain\Project\Collection\PropertiesCollection;
use Padawan\Domain\Project\Collection\ConstCollection;

/**
 * @property $properties
 * @property $methods
 * @property $constants
 */
class ClassData
{
    const MODIFIER_PUBLIC    = 1;
    const MODIFIER_PROTECTED = 2;
    const MODIFIER_PRIVATE   = 4;
    const MODIFIER_STATIC    = 8;
    const MODIFIER_ABSTRACT  = 16;
    const MODIFIER_FINAL     = 32;
    public $interfaces = [];
    /** @var Uses */
    public $uses;

    /**
     * @var FQCN
     */
    public $fqcn;
    public $doc             = "";
    public $startLine       = 0;
    public $file            = "";
    public function __construct(FQCN $fqcn, $file)
    {
        $this->fqcn = $fqcn;
        $this->file = $file;
        $this->constants = new ConstCollection($this);
        $this->methods = new MethodsCollection($this);
        $this->properties = new PropertiesCollection($this);
    }

    /**
     * @return ClassData
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return InterfaceData[]
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    public function getName()
    {
        return $this->fqcn->getClassName();
    }
    public function setParent($parent)
    {
        if ($this === $parent) {
            throw new \Exception("Parent class and child class could not be same");
        }
        $this->parent = null;
        if ($parent instanceof ClassData) {
            foreach ($this->methods->all() as $method) {
                if ($method->doc === Comment::INHERIT_MARK) {
                    $parentMethod = $parent->methods->get($method->name);
                    if ($parentMethod instanceof MethodData) {
                        $method->doc = $parentMethod->doc;
                        $method->setReturn($parentMethod->getReturn());
                    }
                }
            }
        }
        $this->parent = $parent;
    }
    public function addInterface($interface)
    {
        $fqcn = $interface instanceof InterfaceData ? $interface->fqcn : $interface;
        $this->interfaces[$fqcn->toString()] = $interface;
    }
    public function addMethod(MethodData $method)
    {
        if ($method->return instanceof FQCN) {
            if ($method->return->getLast() === 'this') {
                $method->return = $this->fqcn;
            }
        }
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
    public function getProp($propName)
    {
        return $this->properties->get($propName);
    }
    public function hasProp($propName)
    {
        return $this->properties->get($propName) !== null;
    }
    public function addProp(ClassProperty $prop)
    {
        $this->properties->add($prop);
    }
    public function addConst($constName)
    {
        $this->constants->add($constName);
    }
    public function __get($name)
    {
        if ($name === 'methods') {
            return $this->methods;
        } elseif ($name === 'properties') {
            return $this->properties;
        } elseif ($name === 'constants') {
            return $this->constants;
        }
    }

    /** @var ClassData */
    private $parent;
    /** @var MethodsCollection */
    private $methods;
    /** @var PropertiesCollection */
    private $properties;
    /** @var ConstCollection */
    private $constants;
}
