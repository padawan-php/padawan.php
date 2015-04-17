<?php

namespace Complete;

use Entity\Completion\Token;
use Entity\Completion\Context;

class ContextResolver{
    const S_VAR                 = '$';
    const S_CLASS               = '->';
    const S_STATIC              = '::';
    const S_USE                 = 'use';
    const S_NAMESPACE           = 'namespace';
    const S_NEW_CLASS           = 'new';
    const S_EXT_CLASS           = 'extends';
    const S_IMPL_INTERFACE      = 'implements';
    protected static $SOFT_TERMINATES = [
        ' ', '(', ')', '[', ']', '\'', '"'
    ];
    protected static $TERMINATES = [
        ';', ',', '.', '='
    ];
    public function getContext($badLine, $column){
        if(empty($badLine)){
            return null;
        }
        $token = new Token;
        $length = strlen($badLine);
        for($i=0; $i<$length ;++$i){
            $curChar = $badLine[$i];
            $token = $this->addChar($token, $curChar);
        }
        $context = new Context($token->prefix, $token->postfix);
        return $this->defineContextType($context, $token);
    }
    public function addChar($token, $curChar){
        $token->postfix .= $curChar;
        if(
            $this->isSimpleSymbol($token->postfix)
            || $this->isTerminableSymbol($token->postfix)
        ){
            $token->updateSymbol();
        }
        elseif(
            !$this->isTerminableSymbol($token->symbol)
            && $this->isSoftTerminateSymbol($curChar)
        ){
            $token = new Token;
        }
        elseif(
            $this->isTerminateSymbol($curChar)
        ){
            $token = new Token;
        }
        return $token;
    }
    protected function defineContextType(Context $context, Token $token){
        printf("\n%s: %s - %s\n", $token->symbol, $token->postfix, $token->prefix);
        switch($token->symbol){
        case self::S_VAR:
            $context->addType(Context::TYPE_VAR);
            break;
        case self::S_CLASS:
            if($token->prefix === '$this'){
                $context->addType(Context::TYPE_THIS);
            }
            else {
                $context->addType(Context::TYPE_CLASS);
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
        case self::S_NEW_CLASS:
        case self::S_EXT_CLASS:
            $context->addType(Context::TYPE_CLASSNAME);
            break;
        case self::S_IMPL_INTERFACE:
            $context->addType(Context::TYPE_INTERFACENAME);
            break;
        }
        return $context;
    }
    protected function isSimpleSymbol($symbol){
        return $this->isSymbol($symbol, self::S_VAR)
            || $this->isSymbol($symbol, self::S_CLASS)
            || $this->isSymbol($symbol, self::S_STATIC);
    }
    protected function isTerminableSymbol($symbol){
        return $this->isSymbol($symbol, self::S_USE)
            || $this->isSymbol($symbol, self::S_NEW_CLASS)
            || $this->isSymbol($symbol, self::S_NAMESPACE);
    }
    protected function isSoftTerminateSymbol($symbol){
        return in_array($symbol, self::$SOFT_TERMINATES);
    }
    protected function isTerminateSymbol($symbol){
        return in_array($symbol, self::$TERMINATES);
    }
    protected function isSymbol($symbol, $check){
        $symbol = strtolower($symbol);
        return strpos($symbol, $check) === strlen($symbol) - strlen($check);
    }
}
