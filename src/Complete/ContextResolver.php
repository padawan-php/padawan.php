<?php

namespace Complete;

use Entity\Completion\Token;
use Entity\Completion\Context;

class ContextResolver{
    public function getContext($badLine){
        if(empty($badLine)){
            throw new \Exception("Could not define empty line context");
        }
        printf("\n%s\n", $badLine);
        $token = $this->getCompletionToken($badLine);
        $context = new Context($token, $token->symbol);
        return $this->defineContextType($context, $token);
    }
    protected function getCompletionToken($badLine){
        $token = new Token;
        $token->type = -1;
        $token->parent = $token;
        if(strpos($badLine, '<?php') === false
            || strpos($badLine, '<?') === false
        ){
            $badLine = '<?php ' . $badLine;
        }
        $symbols = token_get_all($badLine);
        $symbols = array_slice($symbols, 1);
        foreach($symbols AS $symbol){
            $token = $this->addSymbol($token, $symbol);
        }
        return $token;
    }
    protected function addSymbol(Token $parent, $symbol){
        print_r($symbol);
        if(is_array($symbol)){
            $code = $symbol[0];
            $symbol = $symbol[1];
        }
        else {
            $code = $symbol;
        }
        if($code == T_WHITESPACE){
            return $parent;
        }
        if($code === T_STRING || $code === T_NS_SEPARATOR){
            $parent->symbol .= $symbol;
            return $parent;
        }
        if(is_int($code)){
            printf("%s\n", token_name($code));
        }
        $token = new Token;
        if($code === ';' || $code === ','){
            return $parent;
        }
        switch($code){
        case T_VARIABLE:
            $token->symbol = $symbol;
        case T_NAMESPACE:
        case T_USE:
        case T_NEW:
        case T_EXTENDS:
        case T_IMPLEMENTS:
        case T_OBJECT_OPERATOR:
            $token->type = self::$MAP[$code];
            break;
        case ')':
        case ']':
            $token = $parent->parent;
            break;
        default:
            return $parent;
        }
        if($token){
            $parent->addChild($token);
        }
        return $token;
    }
    protected function defineContextType(Context $context, Token $token){
        print_r($token);
        switch($token->type){
        case self::S_VAR:
            $context->addType(Context::TYPE_VAR);
            break;
        case self::S_OBJECT:
            if($token->symbol === '$this'){
                $context->addType(Context::TYPE_THIS);
            }
            else {
                $context->addType(Context::TYPE_OBJECT);
            }
            break;
        case self::S_STATIC:
            $context->addType(Context::TYPE_CLASS_STATIC);
            break;
        case self::S_NAMESPACE:
            $context->addType(Context::TYPE_NAMESPACE);
            break;
        case self::S_USE:
            $context->addType(Context::TYPE_USE);
        case self::S_NEW:
        case self::S_EXTENDS:
            $context->addType(Context::TYPE_CLASSNAME);
            break;
        case self::S_IMPLEMENTS:
            $context->addType(Context::TYPE_INTERFACENAME);
            break;
        }
        return $context;
    }
    const S_VAR                 = '$';
    const S_OBJECT              = '->';
    const S_STATIC              = '::';
    const S_USE                 = 'use';
    const S_NAMESPACE           = 'namespace';
    const S_NEW                 = 'new';
    const S_EXTENDS             = 'extends';
    const S_IMPLEMENTS          = 'implements';

    public static $MAP          = [
        T_VARIABLE              => self::S_VAR,
        T_OBJECT_OPERATOR       => self::S_OBJECT,
        T_STATIC                => self::S_STATIC,
        T_USE                   => self::S_USE,
        T_NAMESPACE             => self::S_NAMESPACE,
        T_NEW                   => self::S_NEW,
        T_EXTENDS               => self::S_EXTENDS,
        T_IMPLEMENTS            => self::S_IMPLEMENTS
    ];
}
