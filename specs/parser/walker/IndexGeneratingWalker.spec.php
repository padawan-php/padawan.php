<?php

use Prophecy\Argument;
use Padawan\Parser\Walker\IndexGeneratingWalker;
use PhpParser\NodeTraverser;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable as NodeVar;
use Padawan\Parser\ClassParser;
use Padawan\Parser\InterfaceParser;
use Padawan\Parser\UseParser;
use Padawan\Parser\NamespaceParser;
use Padawan\Parser\Transformer\FunctionTransformer;
use Padawan\Parser\Transformer\ClassAssignmentTransformer;
use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Project\Node\MethodData;
use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Project\Node\InterfaceData;
use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\FQCN;

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
        $uses = new Uses(new FQCN(""));
        $this->walker->updateFileInfo($uses, "");
    });
    describe('->enterNode()', function(){
        describe('Class node', function(){
            beforeEach(function(){
                $class = new ClassData(new FQCN("Name"), "");
                $class->addMethod(new MethodData("__construct"));
                $class->addMethod(new MethodData("not_construct"));
                $this->classParser->parse(Argument::any(), Argument::any(), Argument::any())
                    ->willReturn($class);
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
                    ->willReturn(new InterfaceData(new FQCN(""), ""));
            });
            it('enteres Interface node', function(){
                expect($this->walker->enterNode(new Interface_("")))->to->equal(null);
            });
            it('tranforms Interface nodes', function(){
            });
        });
        describe('Namespace node', function(){
            it('tranforms namespace node', function(){
                expect($this->walker->enterNode(new Namespace_(new Name("Test"))))->to->equal(null);
            });
        });
        describe('Use node', function(){
            beforeEach(function(){
                $this->useParser->parse(Argument::any())
                    ->willReturn(new Uses);
            });
            it('tranforms use nodes', function(){
                expect($this->walker->enterNode(new Use_([])))->to->equal(null);
            });
        });
        it('skips other nodes', function(){
            expect($this->walker->enterNode(new Echo_([])))->to->equal(NodeTraverser::DONT_TRAVERSE_CHILDREN);
        });
    });
    describe('->leaveNode()', function(){
        describe('when in MethodScope', function(){
            beforeEach(function(){
                $class = new ClassData(new FQCN(""), "");
                $class->addMethod(new MethodData("__construct"));
                $this->classParser->parse(Argument::any(), Argument::any(), Argument::any())
                    ->willReturn($class);
                $this->walker->enterNode(new Class_("Name"));
                $this->walker->enterNode(new ClassMethod("__construct"));
            });
            it('transforms assignment statements in MethodScope', function(){
                $this->walker->leaveNode(new Assign(new NodeVar(""), new NodeVar("")));
                $this->classAssignmentTransformer->transform(
                    Argument::any(),
                    Argument::any(),
                    Argument::any(),
                    Argument::any()
                )->shouldHaveBeenCalled();
            });
        });
        it('doesn\'t transform assignment statements when not in MethodScope', function(){
            $this->walker->leaveNode(new Assign(new NodeVar(""), new NodeVar("")));
            $this->classAssignmentTransformer->transform(
                Argument::any(),
                Argument::any(),
                Argument::any(),
                Argument::any()
            )->shouldNotBeenCalled();
        });
    });
});
