<?php

namespace Parser;

use DTO\FQCN;
use Utils\PathResolver;
use PhpParser\Parser AS PhpParser;
use PhpParser\NodeTraverser AS Traverser;
use DTO\ClassData;
use DTO\MethodData;
use \PhpParser\Node\Stmt\Class_;
use \PhpParser\Node\Stmt\ClassMethod;

class ClassParser{
    private $parsedClasses = [];
    /** @var PathResolver */
    private $path;
    /** @var PhpParser */
    private $parser;
    /** @var Traverser */
    private $traverser;
    /** @var Visitor\Visitor */
    private $visitor;

    public function __construct(PhpParser $parser = null, PathResolver $path = null){
        $this->path             = $path;
        $this->parser           = $parser;
    }
    public function setParser(PhpParser $parser){
        $this->parser = $parser;
    }
    public function setTraverser(Traverser $traverser){
        $this->traverser = $traverser;
    }
    public function setVisitor(Visitor\Visitor $visitor){
        $this->visitor = $visitor;
        $this->visitor->setParser($this);
        $this->traverser->addVisitor($this->visitor);
    }
    public function parseFQCN($fqcn){
        $regex = '/(.*)(?=\\\\(\w+)$)|(.*)/';
        $ret = preg_match($regex, $fqcn, $matches);
        if(!$ret) {
            throw new \Exception("Error while parsing FQCN");
        }
        return new \DTO\FQCN(
            count($matches) == 3 ? $matches[2] : $matches[3],
            count($matches) == 3 ? $matches[1] : ""
        );
    }
    public function parseFile(FQCN $fqcn, $file){
        $file = $this->path->getAbsolutePath($file);
        $classData = new ClassData($fqcn, $file);

        $content = $this->path->load($file);
        try{
            $ast = $this->parser->parse($content);

            $this->visitor->setClassData($classData);
            $this->traverser->traverse($ast);
        }
        catch(\Exception $e){
            printf("Parsing failed in file %s\n", $file);
        }
        return $classData;
    }
    public function parseInterface(){}
    public function parseClass(Class_ $node, ClassData $classData){
        $classData->startLine = $node->getAttribute("startLine");
        $this->parseMethods($node->getMethods(), $classData);
        $this->parseProperties();
    }
    protected function parseMethods(array $methods, ClassData $classData){
        foreach($methods AS $method){
            $classData->addMethod($this->parseMethod($method));
        }
    }
    protected function parseMethod(ClassMethod $methodAST){
        $method = new MethodData($methodAST->name);
        $method->startLine = $methodAST->getAttribute("startLine");
        $comments = $methodAST->getAttribute("comments");
        $method->doc = $comments[count($comments)-1];
        return $method;
    }
    protected function parseMethodArgument(){}
    protected function parseProperties(){}
}
