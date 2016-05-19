<?php

use Padawan\Domain\Scope;
use Padawan\Parser\UseParser;
use Padawan\Domain\Project\FQN;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Index;
use PhpParser\Node\Expr\MethodCall;
use Padawan\Domain\Scope\FileScope;
use PhpParser\Node\Expr\PropertyFetch;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Project\Node\MethodData;
use PhpParser\Node\Expr\Variable as NodeVar;
use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Project\Node\ClassProperty;
use Padawan\Framework\Domain\Project\InMemoryIndex;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Padawan\Framework\Complete\Resolver\NodeTypeResolver;

function createClass($classFQN, $fqcn) {
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

describe('NodeTypeResolver', function() {
    beforeEach(function() {
        $logger = new Logger('spec');
        $logger->pushHandler(new NullHandler);
        $this->resolver = new NodeTypeResolver($logger, new UseParser, new EventDispatcher);
        $this->scope = new FileScope(new FQN);
        $this->index = new InMemoryIndex;
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
    describe('->getType()', function() {
        it('returns variable type from scope', function() {
            $node = new NodeVar($this->var->getName());
            expect($this->resolver->getLastChainNodeType($node, $this->index, $this->scope))
                ->to->equal($this->var->getType());
        });
        describe('Properties', function() {
            beforeEach(function() {
                $this->node = new PropertyFetch(
                    new NodeVar(
                        $this->var->getName()
                    ),
                    ""
                );
            });
            it('returns null for unknown property', function() {
                $this->node->name = 'param';
                expect($this->resolver->getLastChainNodeType($this->node, $this->index, $this->scope))
                    ->to->be->null;
            });
            it('returns type for known property', function() {
                $this->node->name = 'param2';
                expect($this->resolver->getLastChainNodeType($this->node, $this->index, $this->scope))
                    ->to->equal($this->anotherFQCN);
            });
        });
        describe('Method', function() {
            beforeEach(function() {
                $this->node = new MethodCall(new NodeVar($this->var->getName()), "");
            });
            it('returns null for unknown method', function() {
                $this->node->name = 'method';
                expect($this->resolver->getLastChainNodeType($this->node, $this->index, $this->scope))
                    ->to->be->null;
            });
            it('returns type for known method', function() {
                $this->node->name = 'method2';
                expect($this->resolver->getLastChainNodeType($this->node, $this->index, $this->scope))
                    ->to->equal($this->anotherFQCN);
            });
        });
        describe('Complex', function() {
            beforeEach(function() {
                $this->node = new MethodCall(
                    new PropertyFetch(
                        new MethodCall(
                            new NodeVar(
                                $this->var->getName()
                            ),
                            "method2"
                        ),
                        "param2"
                    ),
                    "method2"
                );
            });
            it('returns type for known property in complex chain', function() {
                $node = new PropertyFetch($this->node, "param2");
                expect($this->resolver->getLastChainNodeType($node, $this->index, $this->scope))
                    ->to->equal($this->var->getType());
            });
        });
        describe('Function call', function(){
            beforeEach(function() {
                $this->node = new FuncCall(new Name('functionName'));
                $function = new FunctionData('functionName');
                $function->setReturn($this->var->getType());
                $this->index->addFunction($function);
            });
            it("returns type after method call", function() {
                expect($this->resolver->getLastChainNodeType($this->node, $this->index, $this->scope))
                    ->to->equal($this->var->getType());
            });
        });
    });
});
