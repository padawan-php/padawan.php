<?php

use Parser\UseParser;
use Entity\Node\Uses;
use Entity\FQCN;

describe('UseParser', function(){
    beforeEach(function(){
        $this->useParser = new UseParser;
        $this->uses = new Uses(
            $this->useParser->parseFQCN('Parser')
        );
        $this->useParser->setUses($this->uses);
    });
    describe('parseFQCN()', function(){
        it('returns instance of FQCN', function(){
            expect($this->useParser->parseFQCN('App'))
                ->to->be->an->instanceof(FQCN::class);
        });
        it('splits complex name by \\', function(){
            $fqcn = $this->useParser->parseFQCN(Uses::class);
            expect($fqcn->getParts())->to->equal([
                'Entity',
                'Node',
                'Uses'
            ]);
        });
        it('creates from single name', function(){
            $fqcn = $this->useParser->parseFQCN('App');
            expect($fqcn->getParts())->to->equal([
                'App'
            ]);
        });
        it('works with absolute names', function(){
            $fqcn = $this->useParser->parseFQCN('\App');
            expect($fqcn->getParts())->to->equal([
                'App'
            ]);
        });
        it('creates valid FQCN', function(){
            $fqcn = $this->useParser->parseFQCN(Uses::class);
            expect($fqcn->toString())->to->equal(Uses::class);
        });
        it('works with array-type', function(){
            $fqcn = $this->useParser->parseFQCN(Uses::class.'[]');
            expect($fqcn->getParts())->to->equal([
                'Entity',
                'Node',
                'Uses'
            ]);
            expect($fqcn->isArray())->to->be->true;
        });
        it('works with empty string', function(){
            $fqcn = $this->useParser->parseFQCN('');
            expect($fqcn)->to->be->an->instanceof(FQCN::class);
        });
        describe('Scalar types', function(){
            $scalars = [
                'string', 'int', 'float',
                'array', 'bool', 'object',
                'void', 'mixed'
            ];
            foreach($scalars AS $scalar){
                it('creates valid parts from ' . $scalar, function() use ($scalar){
                    $fqcn = $this->useParser->parseFQCN($scalar);
                    expect($fqcn->getParts())->to->equal([
                        $scalar
                    ]);
                });
                it('creates non-array fqcn from ' . $scalar, function() use ($scalar){
                    $fqcn = $this->useParser->parseFQCN($scalar);
                    expect($fqcn->isArray())->to->be->false;
                });
                it('creates scalar fqcn from ' . $scalar, function() use ($scalar){
                    $fqcn = $this->useParser->parseFQCN($scalar);
                    expect($fqcn->isScalar())->to->be->true;
                });
            }
        });
    });
    describe('parseType()', function(){
        beforeEach(function(){
            $this->uses->add(
                $this->useParser->parseFQCN(Uses::class)
            );
            $this->uses->add(
                $this->useParser->parseFQCN(FQCN::class)
            );
        });
        it('returns FQCN from uses if exists', function(){
            $fqcn = $this->useParser->parseType('FQCN');
            expect($fqcn->toString())->to->equal(FQCN::class);
        });
        it('works with scalars', function(){
            $fqcn = $this->useParser->parseType('string');
            expect($fqcn->isScalar())->to->be->true;
        });
        it('works with absolute names', function(){
            $fqcn = $this->useParser->parseType('\Entity\Project');
            expect($fqcn->getParts())->to->equal([
                'Entity',
                'Project'
            ]);

        });
    });
});
