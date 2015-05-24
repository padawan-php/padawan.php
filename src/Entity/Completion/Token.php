<?php

namespace Entity\Completion;

class Token {
    private $symbol = "";
    private $type   = 0;

    public function __construct($code, $symbol){
        $this->add($code, $symbol);
    }

    public function add($code, $symbol){
        switch($code){
        case T_WHITESPACE:
            $this->addType(self::T_WHITESPACE);
        case T_NS_SEPARATOR:
        case T_STRING:
            $this->addType(self::T_CONTINUE_PROCESS);
            break;
        case T_VARIABLE:
        case T_DOUBLE_COLON:
        case T_OBJECT_OPERATOR:
            if($this->isWhitespace()){
                $this->addType(self::T_UNKNOWN);
            }
        case T_NAMESPACE:
        case T_USE:
        case T_NEW:
        case T_EXTENDS:
        case T_IMPLEMENTS:
            $this->removeType(self::T_CONTINUE_PROCESS);
            $this->addType(self::$MAP[$code]);
            break;
        case ';':
        case ',':
        case '=':
        case ')':
        case ']':
            $this->addType(self::T_TERMINATE);
            break;
        default:
            $this->addType(self::T_UNKNOWN);
        }
        if(!$this->isReady()){
            $this->symbol = $symbol . $this->symbol;
        }
    }

    public function getSymbol(){
        return $this->symbol;
    }

    public function getType(){
        return $this->type;
    }

    public function isUnknown(){
        return (bool) ($this->type & self::T_UNKNOWN);
    }

    public function isReady(){
        return !((bool) ($this->type & self::T_CONTINUE_PROCESS));
    }

    public function isTerminate(){
        return (bool) ($this->type & self::T_TERMINATE);
    }

    public function isObjectOperator(){
        return (bool) ($this->type & self::T_OBJECT_OPERATOR);
    }

    public function isStaticOperator(){
        return (bool) ($this->type & self::T_STATIC_OPERATOR);
    }

    public function isUseOperator(){
        return (bool) ($this->type & self::T_USE_OPERATOR);
    }

    public function isNamespaceOperator(){
        return (bool) ($this->type & self::T_NAMESPACE_OPERATOR);
    }

    public function isExtendsOperator(){
        return (bool) ($this->type & self::T_EXTENDS_OPERATOR);
    }

    public function isImplementsOperator(){
        return (bool) ($this->type & self::T_IMPLEMENTS_OPERATOR);
    }

    public function isNewOperator(){
        return (bool) ($this->type & self::T_NEW_OPERATOR);
    }

    public function isVar(){
        return (bool) ($this->type & self::T_VAR);
    }

    public function isWhitespace(){
        return (bool) ($this->type & self::T_WHITESPACE);
    }

    protected function addType($type){
        $this->type |= $type;
    }

    protected function removeType($type){
        if((bool) ($this->type & $type)){
            $this->type ^= $type;
        }
    }

    const T_UNKNOWN             = -1;
    const T_CONTINUE_PROCESS    = 1;
    const T_TERMINATE           = 2;
    const T_OBJECT_OPERATOR     = 4;
    const T_STATIC_OPERATOR     = 8;
    const T_USE_OPERATOR        = 16;
    const T_NAMESPACE_OPERATOR  = 32;
    const T_EXTENDS_OPERATOR    = 64;
    const T_IMPLEMENTS_OPERATOR = 128;
    const T_NEW_OPERATOR        = 256;
    const T_VAR                 = 512;
    const T_WHITESPACE          = 1024;

    protected static $MAP = [
        T_VARIABLE              => Token::T_VAR,
        T_OBJECT_OPERATOR       => Token::T_OBJECT_OPERATOR,
        T_DOUBLE_COLON          => Token::T_STATIC_OPERATOR,
        T_USE                   => Token::T_USE_OPERATOR,
        T_NAMESPACE             => Token::T_NAMESPACE_OPERATOR,
        T_NEW                   => Token::T_NEW_OPERATOR,
        T_EXTENDS               => Token::T_EXTENDS_OPERATOR,
        T_IMPLEMENTS            => Token::T_IMPLEMENTS_OPERATOR
    ];
}
