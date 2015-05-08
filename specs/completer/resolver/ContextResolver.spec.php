<?php

use Complete\Resolver\ContextResolver;
use Complete\Resolver\NodeTypeResolver;
use Parser\ErrorFreePhpParser;
use Parser\UseParser;
use PhpParser\Lexer;
use Entity\Completion\Context;
use Entity\Index;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

describe('ContextResolver', function(){
    beforeEach(function(){
        $logger = new Logger('spec');
        $logger->pushHandler(new NullHandler);
        $this->index = new Index;
        $this->parser = new ErrorFreePhpParser(new Lexer);
        $this->typeResolver = new NodeTypeResolver($logger, new UseParser);
        $this->resolver = new ContextResolver($this->parser, $this->typeResolver, $logger);
        $this->dummyLine = '$obj->getMethod()->';
    });
    describe('->getContext()', function(){
        it('throws exception on empty line', function(){
            expect([$this->resolver, 'getContext'])
                ->with('', $this->index)->to->throw('Exception');
        });
        it('returns Context instance', function(){
            $result = $this->resolver->getContext($this->dummyLine);
            expect($result)->to->be->an->instanceof(Context::class);
        });
        describe('Namespace', function(){
            it('has type namespace after namespace symbol', function(){
                $context = $this->resolver->getContext('namespace ');
                expect($context->isNamespace())->to->be->true;
            });
            it('has type namespace after namespace symbol with TString', function(){
                $context = $this->resolver->getContext('namespace SomeName');
                expect($context->isNamespace())->to->be->true;
            });
            it('has type namespace after namespace symbol with TString and separator', function(){
                $context = $this->resolver->getContext('namespace SomeName\AndOther\Name');
                expect($context->isNamespace())->to->be->true;
            });
            it('hasn\'t type namespace after ;', function(){
                $context = $this->resolver->getContext('namespace SomeName\AndOther\Name;');
                expect($context->isNamespace())->to->be->false;
            });
        });
        describe('Use', function(){
            it('has type use after use symbol', function(){
                $context = $this->resolver->getContext('use ');
                expect($context->isUse())->to->be->true;
            });
            it('has type use after use symbol with TString', function(){
                $context = $this->resolver->getContext('use SomeName');
                expect($context->isUse())->to->be->true;
            });
            it('has type use after use symbol with TString and separator', function(){
                $context = $this->resolver->getContext('use SomeName\AndOther\Name');
                expect($context->isUse())->to->be->true;
            });
            it('hasn\'t type use after ;', function(){
                $context = $this->resolver->getContext('use SomeName\AndOther\Name;');
                expect($context->isUse())->to->be->false;
            });
        });
        describe('Object', function(){
            it('has type object after object operator', function(){
                $context = $this->resolver->getContext('$var->');
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator and $this', function(){
                $context = $this->resolver->getContext('$this->');
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator with complex prefix', function(){
                $context = $this->resolver->getContext('$var->getMethod()->param->');
                expect($context->isObject())->to->be->true;
            });
            it('has type object after object operator with TString', function(){
                $context = $this->resolver->getContext('$var->param');
                expect($context->isObject())->to->be->true;
            });
            it('hasn\'t type object after object operator with TString and separator', function(){
                $context = $this->resolver->getContext('$var->param;');
                expect($context->isObject())->to->be->false;
            });
            it('hasn\'t type object after object operator with TString and space', function(){
                /** @var Context $context */
                $context = $this->resolver->getContext('$var->param ');
                expect($context->isObject())->to->be->false;
            });
        });
    });
});
