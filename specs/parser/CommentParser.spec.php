<?php

use Parser\CommentParser;
use Parser\UseParser;
use Entity\Node\Comment;
use Entity\Node\Uses;
use Entity\FQCN;

describe('CommentParser', function(){
    beforeEach(function(){
        $this->useParser = new UseParser;
        $this->uses = new Uses(
            $this->useParser->parseFQCN('Entity\Node')
        );
        $this->useParser->setUses(
            $this->uses
        );
        $this->uses->add($this->useParser->parseFQCN(Comment::class));
        $this->parser = new CommentParser($this->useParser);
        $this->simpleDoc = <<<'DOCBLOCK'
/**
 * This is a short description
 *
 * This is a long description
 *
 * @param MethodParam $myParamName A test param
 * @param Comment $anotherParam
 * @throws \Exception
 * @return Comment
 */
DOCBLOCK;
        $this->comment = $this->parser->parse($this->simpleDoc);
    });
    describe('parse()', function(){
        it('returns Comment', function(){
            $result = $this->comment;
            expect($result)->to->be->an->instanceof(Comment::class);
        });
        it('creates vars for all params', function(){
            $comment = $this->comment;
            expect(count($comment->getVars()))->to->equal(2);
        });
        it('returns FQCN', function(){
            $comment = $this->comment;
            expect($comment->getReturn())->to->be->an->instanceof(FQCN::class);
            expect($comment->getReturn()->toString())->to->equal('Entity\Node\Comment');
        });
    });
    describe('createMethodParam()', function(){
        it('sets var name', function(){
            $comment = $this->comment;
            $var = array_shift($comment->getVars());
            expect($var->getName())->to->equal('myParamName');
        });
        it('sets var type', function(){
            $comment = $this->comment;
            $var = array_pop($comment->getVars());
            expect($var->getType())->to->be->an->instanceof(FQCN::class);
            expect($var->getType()->toString())->to->equal(
                'Entity\Node\Comment'
            );
        });
    });
    describe('trimComment()', function(){
        it('removes * and spaces', function(){
            $result = $this->comment;
            $trimmedDoc = <<<'DOCBLOCK'

This is a short description

This is a long description

@param MethodParam $myParamName A test param
@param Comment $anotherParam
@throws \Exception
@return Comment

DOCBLOCK;
            expect($result->getDoc())->to->equal($trimmedDoc);
        });
    });
});
