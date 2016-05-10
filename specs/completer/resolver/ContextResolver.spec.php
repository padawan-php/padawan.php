<?php

use Padawan\Framework\Complete\Resolver\ContextResolver;
use Padawan\Framework\Complete\Resolver\NodeTypeResolver;
use Padawan\Parser\ErrorFreePhpParser;
use Padawan\Parser\UseParser;
use PhpParser\Lexer;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Project\Index;
use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

describe('ContextResolver', function() {
    beforeEach(function() {
        $logger = new Logger('spec');
        $logger->pushHandler(new NullHandler);
        $this->index = new Index;
        $this->parser = new ErrorFreePhpParser(new Lexer);
        $this->useParser = new UseParser;
        $this->typeResolver = new NodeTypeResolver($logger, $this->useParser, new EventDispatcher);
        $this->resolver = new ContextResolver($this->parser, $this->typeResolver, $logger, $this->useParser);
        $this->dummyLine = '$obj->getMethod()->';
    });
    describe('->getContext()', function() {
        it('returns Context instance', function() {
            $result = $this->resolver->getContext($this->dummyLine, $this->index);
            expect($result)->to->be->an->instanceof(Context::class);
        });
        it('returns empty Context for empty line', function() {
            $context = $this->resolver->getContext("", $this->index);
            expect($context->isEmpty())->to->be->true;
        });
        describe("Name", function () {
            it('has type name after T_STRING', function () {
                $context = $this->resolver->getContext("str_r", $this->index);
                expect($context->isString())->to->be->true;
            });
        });
        describe('Namespace', function() {
            it('has type namespace after namespace symbol', function() {
                $context = $this->resolver->getContext('namespace ', $this->index);
                expect($context->isNamespace())->to->be->true;
            });
            it('has type namespace after namespace symbol with TString', function() {
                $context = $this->resolver->getContext('namespace SomeName', $this->index);
                expect($context->isNamespace())->to->be->true;
            });
            it('has type namespace after namespace symbol with TString and separator', function() {
                $context = $this->resolver->getContext('namespace SomeName\AndOther\Name', $this->index);
                expect($context->isNamespace())->to->be->true;
            });
            it('hasn\'t type namespace after ;', function() {
                $context = $this->resolver->getContext('namespace SomeName\AndOther\Name;', $this->index);
                expect($context->isNamespace())->to->be->false;
            });
        });
        describe('Use', function() {
            it('has type use after use symbol', function() {
                $context = $this->resolver->getContext('use ', $this->index);
                expect($context->isUse())->to->be->true;
            });
            it('has type use after use symbol with TString', function() {
                $context = $this->resolver->getContext('use SomeName', $this->index);
                expect($context->isUse())->to->be->true;
            });
            it('has type use after use symbol with TString and separator', function() {
                $context = $this->resolver->getContext('use SomeName\AndOther\Name', $this->index);
                expect($context->isUse())->to->be->true;
            });
            it('hasn\'t type use after ;', function() {
                $context = $this->resolver->getContext('use SomeName\AndOther\Name;', $this->index);
                expect($context->isUse())->to->be->false;
            });
        });
        describe('Object', function() {
            it('has type object after object operator', function() {
                $context = $this->resolver->getContext('$var->', $this->index);
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator and space', function() {
                $context = $this->resolver->getContext('$var-> ', $this->index);
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator and $this', function() {
                $context = $this->resolver->getContext('$this->', $this->index);
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator with complex prefix', function() {
                $context = $this->resolver->getContext('$var->getMethod()->param->', $this->index);
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator with TString', function() {
                $context = $this->resolver->getContext('$var->param', $this->index);
                expect($context->isObject())->to->be->true;
            });
            it('hasn\'t type object after object operator with TString and separator', function() {
                $context = $this->resolver->getContext('$var->param;', $this->index);
                expect($context->isObject())->to->be->false;
            });
            it('hasn\'t type object after object operator with (', function() {
                $context = $this->resolver->getContext('$var->param(', $this->index);
                expect($context->isObject())->to->be->false;
            });
            it('hasn\'t type object after object operator with TString and space', function() {
                /** @var Context $context */
                $context = $this->resolver->getContext('$var->param ', $this->index);
                expect($context->isObject())->to->be->false;
            });
        });
        describe("Method call", function() {
            it('has type method call after (', function() {
                $context = $this->resolver->getContext('$var->method(', $this->index);
                expect($context->isMethodCall())->to->be->true;
            });
        });
        describe("Variable", function() {
            it('has type variable after $', function() {
                $context = $this->resolver->getContext('$', $this->index);
                expect($context->isVar())->to->be->true;
            });
            it('has type variable after $ with TString', function() {
                $context = $this->resolver->getContext('$var', $this->index);
                expect($context->isVar())->to->be->true;
            });
            it('hasn\'t type variable after space symbol', function() {
                $context = $this->resolver->getContext('$var ', $this->index);
                expect($context->isVar())->to->be->false;
            });
            it('hasn\'t type variable after - symbol', function() {
                $context = $this->resolver->getContext('$var-', $this->index);
                expect($context->isVar())->to->be->false;
            });
        });
    });
});
