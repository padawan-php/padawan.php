<?php

use Entity\FQCN;
use Entity\FQN;

describe('FQCN', function(){
    describe('__construct()', function(){
        it('creates parts from string with class', function(){
            $fqn = new FQCN('SomeClassName', 'Some\\Long\\Parts\\To\\Name');
            expect($fqn->getParts())->to->equal([
                'Some',
                'Long',
                'Parts',
                'To',
                'Name',
                'SomeClassName'
            ]);
        });
        it('creates parts from array', function(){
            $parts = ['Some', 'Long', 'Parts'];
            $fqn = new FQCN('ClassName', $parts);
            $parts[] = 'ClassName';
            expect($fqn->getParts())->to->equal($parts);
        });
        it('FQCN with class name only', function(){
            $fqn = new FQN('ClassName');
            expect($fqn->getParts())->to->equal(['ClassName']);
        });
    });
    describe('->join()', function(){
        it('joins another FQCN', function(){
            $fqn = new FQCN('ClassName', 'Some\\Long\\Path');
            $join = new FQCN('AnotherName', 'Another\\Long\\Name');
            expect($fqn->join($join)->getParts())->to->equal([
                'Some',
                'Long',
                'Path',
                'ClassName',
                'Another',
                'Long',
                'Name',
                'AnotherName'
            ]);
        });
        it('joins FQN', function(){
            $fqn = new FQCN('ClassName', 'Some\\Long\\Path\\Another');
            $join = new FQN('Another\\Long\\Name');
            expect($fqn->join($join)->getParts())->to->equal([
                'Some',
                'Long',
                'Path',
                'Another',
                'ClassName',
                'Another',
                'Long',
                'Name'
            ]);
        });
    });
    describe('->toString()', function(){
        it('returns valid string', function(){
            $str = 'Some\\Long\\Path\\To\\Name';
            $fqn = new FQCN('ClassName', $str);
            expect($fqn->toString())->to->equal($str . '\\ClassName');
        });
    });
});
