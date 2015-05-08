<?php

use Complete\Resolver\NodeTypeResolver;
use Entity\Completion\Scope;
use Entity\FQCN;
use Entity\Index;
use Entity\Node\ClassData;
use Entity\Node\ClassProperty;
use Entity\Node\MethodData;
use Entity\Node\Variable;
use Parser\UseParser;
use PhpParser\Node\Expr\Variable as NodeVar;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\MethodCall;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

function createClass($classFQN, $fqcn){
    $class = new ClassData($classFQN, 'dummy/path/class.php');
    $method = new MethodData('method2');
    $method->setType(ClassData::MODIFIER_PUBLIC);
    $param = new ClassProperty('param2');
    $param->type = $fqcn;
    $method->setReturn($fqcn);
    $class->addMethod($method);
    $class->addProp($param);
    return $class;
}

describe('NodeTypeResolver', function(){
    beforeEach(function(){
        $logger = new Logger('spec');
        $logger->pushHandler(new NullHandler);
        $this->resolver = new NodeTypeResolver($logger, new UseParser);
        $this->scope = new Scope;
        $this->index = new Index;
        $this->var = new Variable('test');
        $fqcn = new FQCN('ClassName', 'Some\\Path');
        $fqcn2 = new FQCN('AnotherClassName', 'Another\\Path\\To\\It');
        $this->anotherFQCN = $fqcn2;
        $this->var->setType($fqcn);
        $this->scope->addVar($this->var);
        $class = createClass($fqcn, $fqcn2);
        $class2 = createClass($fqcn2, $fqcn);
        $this->index->addClass($class);
        $this->index->addClass($class2);
    });
    describe('->getType()', function(){
        it('returns variable type from scope', function(){
            $node = new NodeVar;
            $node->name = $this->var->getName();
            expect($this->resolver->getChainType($node, $this->index, $this->scope))
                ->to->equal($this->var->getType());
        });
        describe('Properties', function(){
            beforeEach(function(){
                $this->node = new PropertyFetch;
                $this->node->var = new NodeVar;
                $this->node->var->name = $this->var->getName();
            });
            it('returns null for unknown property', function(){
                $this->node->name = 'param';
                expect($this->resolver->getChainType($this->node, $this->index, $this->scope))
                    ->to->be->null;
            });
            it('returns type for known property', function(){
                $this->node->name = 'param2';
                expect($this->resolver->getChainType($this->node, $this->index, $this->scope))
                    ->to->equal($this->anotherFQCN);
            });
        });
        describe('Method', function(){
            beforeEach(function(){
                $this->node = new MethodCall;
                $this->node->var = new NodeVar;
                $this->node->var->name = $this->var->getName();
            });
            it('returns null for unknown method', function(){
                $this->node->name = 'method';
                expect($this->resolver->getChainType($this->node, $this->index, $this->scope))
                    ->to->be->null;
            });
            it('returns type for known method', function(){
                $this->node->name = 'method2';
                expect($this->resolver->getChainType($this->node, $this->index, $this->scope))
                    ->to->equal($this->anotherFQCN);
            });
        });
        describe('Complex', function(){
            beforeEach(function(){
                $this->node = new MethodCall;
                $this->node->name = 'method2';
                $this->node->var = new PropertyFetch;
                $this->node->var->name = 'param2';
                $this->node->var->var = new MethodCall;
                $this->node->var->var->name = 'method2';
                $this->node->var->var->var = new NodeVar;
                $this->node->var->var->var->name = $this->var->getName();
            });
            it('returns type for known property in complex chain', function(){
                $node = new PropertyFetch;
                $node->var = $this->node;
                $node->name = 'param2';
                expect($this->resolver->getChainType($node, $this->index, $this->scope))
                    ->to->equal($this->var->getType());
            });
        });
    });
});
