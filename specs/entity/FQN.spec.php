<?php

use Entity\FQN;

describe('FQN', function(){
    describe('__construct()', function(){
        it('creates parts from string', function(){
            $fqn = new FQN('Some\\Long\\Parts\\To\\Name');
            expect($fqn->getParts())->to->equal([
                'Some',
                'Long',
                'Parts',
                'To',
                'Name'
            ]);
        });
        it('creates parts from array', function(){
            $parts = ['Some', 'Long', 'Parts'];
            $fqn = new FQN($parts);
            expect($fqn->getParts())->to->equal($parts);
        });
        it('creates empty parts on empty call', function(){
            $fqn = new FQN;
            expect($fqn->getParts())->to->equal([]);
        });
    });
    describe('->join()', function(){
        it('joins another FQN', function(){
            $fqn = new FQN('Some\\Long\\Path');
            $join = new FQN('Another\\Long\\Name');
            expect($fqn->join($join)->getParts())->to->equal([
                'Some',
                'Long',
                'Path',
                'Another',
                'Long',
                'Name'
            ]);
        });
        it('joins child FQN', function(){
            $fqn = new FQN('Some\\Long\\Path\\Another');
            $join = new FQN('Another\\Long\\Name');
            expect($fqn->join($join)->getParts())->to->equal([
                'Some',
                'Long',
                'Path',
                'Another',
                'Long',
                'Name'
            ]);
        });
    });
    describe('->toString()', function(){
        it('returns valid string', function(){
            $str = 'Some\\Long\\Path\\To\\Name';
            $fqn = new FQN($str);
            expect($fqn->toString())->to->equal($str);
        });
    });
});
