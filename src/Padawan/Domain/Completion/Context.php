<?php

namespace Padawan\Domain\Completion;

use Padawan\Domain\Scope;

class Context
{
    const T_USE              = 2;
    const T_NAMESPACE        = 4;
    const T_OBJECT           = 8;
    const T_CLASSNAME        = 16;
    const T_INTERFACENAME    = 32;
    const T_THIS             = 64;
    const T_CLASS_STATIC     = 128;
    const T_CLASS_METHODS    = 256;
    const T_METHOD_CALL      = 512;
    const T_VAR              = 1024;
    const T_ANY_NAME         = 2048;
    const T_GLOBAL           = 4096;

    private $type            = 0;
    private $token;
    private $scope;
    private $data;

    public function __construct(Scope $scope, Token $token) {
        $this->scope = $scope;
        $this->setToken($token);
    }
    public function setToken(Token $token) {
        $this->token = $token;
        if ($token->isVar()) {
            $this->addType(self::T_VAR);
        } elseif ($token->isGlobal()) {
            $this->addType(self::T_GLOBAL);
        } elseif ($token->isObjectOperator()) {
            $this->addType(self::T_OBJECT);
        } elseif ($token->isStaticOperator()) {
            $this->addType(self::T_CLASS_STATIC);
        } elseif ($token->isNamespaceOperator()) {
            $this->addType(self::T_NAMESPACE);
            $this->setData($token->getSymbol());
        } elseif ($token->isUseOperator()) {
            $this->addType(self::T_USE);
            $this->setData($token->getSymbol());
        } elseif ($token->isNewOperator()) {
            $this->addType(self::T_CLASSNAME);
            $this->setData($token->getSymbol());
        } elseif ($token->isExtendsOperator()) {
            $this->addType(self::T_CLASSNAME);
            $this->setData($token->getSymbol());
        } elseif ($token->isImplementsOperator()) {
            $this->addType(self::T_INTERFACENAME);
            $this->setData($token->getSymbol());
        } elseif ($token->isMethodCall()) {
            $this->addType(self::T_METHOD_CALL);
        } elseif ($token->isString()) {
            $this->addType(self::T_ANY_NAME);
            $this->setData($token->getSymbol());
        } elseif ($token->isTerminate()) {
            $this->setData($token->getSymbol());
        }
    }

    public function setData($data) {
        $this->data = $data;
    }
    public function getData() {
        return $this->data;
    }
    public function addType($type) {
        $this->type = $this->type | $type;
    }

    /**
     * @return Scope
     */
    public function getScope() {
        return $this->scope;
    }

    /**
     * @return Token
     */
    public function getToken() {
        return $this->token;
    }
    public function isEmpty() {
        return $this->type === 0;
    }
    public function isVar() {
        return (bool) ($this->type & self::T_VAR);
    }
    public function isGlobal() {
        return (bool) ($this->type & self::T_GLOBAL);
    }
    public function isUse() {
        return (bool) ($this->type & self::T_USE);
    }
    public function isNamespace() {
        return (bool) ($this->type & self::T_NAMESPACE);
    }
    public function isObject() {
        return (bool) ($this->type & self::T_OBJECT);
    }
    public function isClassName() {
        return (bool) ($this->type & self::T_CLASSNAME);
    }
    public function isInterfaceName() {
        return (bool) ($this->type & self::T_INTERFACENAME);
    }
    public function isThis() {
        return (bool) ($this->type & self::T_THIS);
    }
    public function isClassStatic() {
        return (bool) ($this->type & self::T_CLASS_STATIC);
    }
    public function isClassMethods()
    {
        return (bool) ($this->type & self::T_CLASS_METHODS);
    }
    public function isMethodCall()
    {
        return (bool) ($this->type & self::T_METHOD_CALL);
    }
    public function isString()
    {
        return (bool) ($this->type & self::T_ANY_NAME);
    }
}
