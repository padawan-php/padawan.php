<?php

use Prophecy\Argument;
use Parser\Walker\IndexGeneratingWalker;
use PhpParser\NodeTraverser;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Expr\Assign;
use Parser\ClassParser;
use Parser\InterfaceParser;
use Parser\UseParser;
use Parser\NamespaceParser;
use Parser\Transformer\FunctionTransformer;
use Parser\Transformer\ClassAssignmentTransformer;
use Domain\Core\Node\ClassData;
use Domain\Core\Node\FunctionData;
use Domain\Core\Node\InterfaceData;
use Domain\Core\Node\Uses;
use Domain\Core\FQCN;

describe('IndexGeneratingWalker', function(){
    beforeEach(function (){
        $this->classParser = $this->getProphet()->prophesize(ClassParser::class);
        $this->interfaceParser = $this->getProphet()->prophesize(InterfaceParser::class);
        $this->useParser = $this->getProphet()->prophesize(UseParser::class);
        $this->namespaceParser = $this->getProphet()->prophesize(NamespaceParser::class);
        $this->functionTransformer = $this->getProphet()->prophesize(FunctionTransformer::class);
        $this->classAssignmentTransformer = $this->getProphet()->prophesize(ClassAssignmentTransformer::class);
        $this->walker = new IndexGeneratingWalker(
            $this->classParser->reveal(),
            $this->interfaceParser->reveal(),
            $this->useParser->reveal(),
            $this->namespaceParser->reveal(),
            $this->functionTransformer->reveal(),
            $this->classAssignmentTransformer->reveal()
        );
        $this->walker->updateFileInfo(new Uses, "");
    });
    describe('->enterNode()', function(){
        describe('Class node', function(){
            beforeEach(function(){
                $this->classParser->parse(Argument::any(), Argument::any(), Argument::any())
                    ->willReturn(new ClassData(new FQCN, ""));
            });
            it('enteres Class node', function(){
                expect($this->walker->enterNode(new Class_("")))->to->equal(null);
            });
            it('transforms Class nodes', function(){
                $this->walker->enterNode(new Class_("Name"));
                $this->classParser->parse(Argument::any(), Argument::any(), Argument::any())
                    ->shouldHaveBeenCalled();
            });
            describe('ClassMethod node', function() {
                beforeEach(function(){
                    $this->walker->enterNode(new Class_("Name"));
                });
                it('enteres Class __construct() method', function(){
                    expect($this->walker->enterNode(new ClassMethod("__construct")))->to->equal(null);
                });
                it('skips Class non-constructor methods', function(){
                    expect($this->walker->enterNode(new ClassMethod("not_construct")))
                        ->to->equal(NodeTraverser::DONT_TRAVERSE_CHILDREN);
                });
            });
        });
        describe('Function node', function(){
            beforeEach(function(){
                $this->functionTransformer->tranform(Argument::any())
                    ->willReturn(new FunctionData(""));
            });
            it('enteres Function node', function(){
                expect($this->walker->enterNode(new Function_("")))->to->equal(null);
            });
            it('transforms Function nodes', function(){
            });
        });
        describe('Interface node', function(){
            beforeEach(function(){
                $this->interfaceParser->parse(Argument::any(), Argument::any(), Argument::any())
                    ->willReturn(new InterfaceData(new FQCN, ""));
            });
            it('enteres Interface node', function(){
                expect($this->walker->enterNode(new Interface_("")))->to->equal(null);
            });
            it('tranforms Interface nodes', function(){
            });
        });
        describe('Namespace node', function(){
            it('tranforms namespace node', function(){
                expect($this->walker->enterNode(new Namespace_("Test")))->to->equal(null);
            });
        });
        describe('Use node', function(){
            beforeEach(function(){
                $this->useParser->parse(Argument::any())
                    ->willReturn(new Uses);
            });
            it('tranforms use nodes', function(){
                expect($this->walker->enterNode(new Use_("Test")))->to->equal(null);
            });
        });
        it('skips other nodes', function(){
            expect($this->walker->enterNode(new Echo_))->to->equal(NodeTraverser::DONT_TRAVERSE_CHILDREN);
        });
    });
    describe('->leaveNode()', function(){
        describe('when in MethodScope', function(){
            beforeEach(function(){
                $this->classParser->parse(Argument::any(), Argument::any(), Argument::any())
                    ->willReturn(new ClassData(new FQCN, ""));
                $this->walker->enterNode(new Class_("Name"));
                $this->walker->enterNode(new ClassMethod("__construct"));
            });
            it('transforms assignment statements in MethodScope', function(){
                $this->walker->leaveNode(new Assign(null, null));
                $this->classAssignmentTransformer->transform(
                    Argument::any(),
                    Argument::any(),
                    Argument::any(),
                    Argument::any()
                )->shouldHaveBeenCalled();
            });
        });
        it('doesn\'t transform assignment statements when not in MethodScope', function(){
            $this->walker->leaveNode(new Assign(null, null));
            $this->classAssignmentTransformer->transform(
                Argument::any(),
                Argument::any(),
                Argument::any(),
                Argument::any()
            )->shouldNotBeenCalled();
        });
    });
});
