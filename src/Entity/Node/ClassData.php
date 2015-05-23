<?php

namespace Entity\Node;

use Entity\FQCN;
use Entity\FQN;
use Entity\Collection\MethodsCollection;
use Entity\Collection\PropertiesCollection;
use Entity\Collection\ConstCollection;

class ClassData{
    const MODIFIER_PUBLIC    =  1;
    const MODIFIER_PROTECTED =  2;
    const MODIFIER_PRIVATE   =  4;
    const MODIFIER_STATIC    =  8;
    const MODIFIER_ABSTRACT  = 16;
    const MODIFIER_FINAL     = 32;
    public $interfaces      = [];
    /** @var Uses */
    public $uses;

    /**
     * @var FQCN
     */
    public $fqcn;
    public $doc             = "";
    public $startLine       = 0;
    public $file            = "";
    public function __construct(FQCN $fqcn, $file){
        $this->fqcn = $fqcn;
        $this->file = $file;
        $this->constants = new ConstCollection($this);
        $this->methods = new MethodsCollection($this);
        $this->properties = new PropertiesCollection($this);
    }

    /**
     * @return ClassData
     */
    public function getParent(){
        return $this->parent;
    }

    /**
     * @return InterfaceData[]
     */
    public function getInterfaces(){
        return $this->interfaces;
    }

    public function getName(){
        return $this->fqcn->getClassName();
    }
    public function setParent($parent){
        $this->parent = $parent;
        if($parent instanceof ClassData){
            foreach($this->methods->all() as $method){
                if($method->doc === Comment::INHERIT_MARK){
                    $parentMethod = $parent->methods->get($method->name);
                    if($parentMethod instanceof MethodData){
                        $method->doc = $parentMethod->doc;
                        $method->setReturn($parentMethod->getReturn());
                    }
                }
            }
        }
    }
    public function addInterface($interface){
        $fqcn = $interface instanceof InterfaceData ? $interface->fqcn : $interface;
        $this->interfaces[$fqcn->toString()] = $interface;
    }
    public function addMethod(MethodData $method){
        $this->methods->add($method);
    }
    public function addProp(ClassProperty $prop){
        $this->properties->add($prop);
    }
    public function addConst($constName){
        $this->constants->add($constName);
    }
    public function __get($name){
        if($name === 'methods'){
            return $this->methods;
        }
        elseif($name === 'properties'){
            return $this->properties;
        }
        elseif($name === 'constants'){
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
