<?php

namespace Entity\Completion;

class Context {
    const TYPE_VAR              = 1;
    const TYPE_USE              = 2;
    const TYPE_NAMESPACE        = 4;
    const TYPE_OBJECT           = 8;
    const TYPE_CLASSNAME        = 16;
    const TYPE_INTERFACENAME    = 32;
    const TYPE_THIS             = 64;
    const TYPE_CLASS_STATIC     = 128;
    const TYPE_CLASS_METHODS    = 256;

    private $type       = 0;
    private $postfix    = "";
    private $token;
    public function __construct(Token $token, $postfix){
        $this->token = $token;
        $this->postfix = $postfix;
    }
    public function addType($type){
        $this->type = $this->type | $type;
    }
    public function getToken(){
        return $this->token;
    }
    public function getPostfix(){
        return $this->postfix;
    }
    public function isEmpty(){
        return $this->type === 0;
    }
    public function isVar(){
        return (bool) ($this->type & self::TYPE_VAR);
    }
    public function isUse(){
        return (bool) ($this->type & self::TYPE_USE);
    }
    public function isNamespace(){
        return (bool) ($this->type & self::TYPE_NAMESPACE);
    }
    public function isObject(){
        return (bool) ($this->type & self::TYPE_OBJECT);
    }
    public function isClassName(){
        return (bool) ($this->type & self::TYPE_CLASSNAME);
    }
    public function isInterfaceName(){
        return (bool) ($this->type & self::TYPE_INTERFACENAME);
    }
    public function isThis(){
        return (bool) ($this->type & self::TYPE_THIS);
    }
    public function isClassStatic(){
        return (bool) $this->type & self::TYPE_CLASS_STATIC;
    }
    public function isClassMethods(){
        return (bool) $this->type & self::TYPE_CLASS_METHODS;
    }
}
