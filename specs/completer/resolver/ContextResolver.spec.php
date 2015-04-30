<?php

use Complete\Resolver\ContextResolver;
use Parser\ErrorFreePhpParser;
use PhpParser\Lexer;
use Entity\Completion\Context;

describe('ContextResolver', function(){
    beforeEach(function(){
        $this->parser = new ErrorFreePhpParser(new Lexer);
        $this->resolver = new ContextResolver($this->parser);
        $this->dummyLine = '$obj->getMethod()->';
    });
    describe('getContext()', function(){
        it('throws exception on empty line', function(){
            expect(function($l){ $this->resolver->getContext($l); })->with('')->to->throw('Exception');
        });
        it('returns Context instance', function(){
            $result = $this->resolver->getContext($this->dummyLine);
            expect($result)->to->be->an->instanceof(Context::class);
        });
    });
});
