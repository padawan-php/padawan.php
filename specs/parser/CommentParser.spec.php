<?php

use Padawan\Parser\CommentParser;
use Padawan\Parser\UseParser;
use Padawan\Domain\Project\Node\Comment;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\FQCN;

describe('CommentParser', function() {
    beforeEach(function() {
        $this->useParser = new UseParser;
        $this->uses = new Uses(
            $this->useParser->parseFQCN('Padawan\Domain\Core\Node')
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
    describe('parse()', function() {
        it('returns Comment', function() {
            $result = $this->comment;
            expect($result)->to->be->an->instanceof(Comment::class);
        });
        it('creates vars for all params', function() {
            $comment = $this->comment;
            expect(count($comment->getVars()))->to->equal(2);
        });
        it('returns FQCN', function() {
            $comment = $this->comment;
            expect($comment->getReturn())->to->be->an->instanceof(FQCN::class);
            expect($comment->getReturn()->toString())->to->equal(Comment::class);
        });
    });
    describe('createMethodParam()', function() {
        it('sets var name', function() {
            $vars = $this->comment->getVars();
            $var = array_shift($vars);
            expect($var->getName())->to->equal('myParamName');
        });
        it('sets var type', function() {
            $vars = $this->comment->getVars();
            $var = array_pop($vars);
            expect($var->getType())->to->be->an->instanceof(FQCN::class);
            expect($var->getType()->toString())->to->equal(
                Comment::class
            );
        });
    });
    describe('trimComment()', function() {
        it('removes * and spaces', function() {
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
