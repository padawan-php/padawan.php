<?php

namespace Parser;

use Entity;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\ClassConst;

class ClassParser{
    private $docParser;
    private $methodsParser;
    private $propertiesParser;
    private $useParser;

    /**
     * Constructor
     */
    public function __construct(
        CommentParser $docParser,
        MethodParser $methodsParser,
        PropertyParser $propertiesParser,
        UseParser $useParser
    )
    {
        $this->docParser = $docParser;
        $this->methodsParser = $methodsParser;
        $this->propertiesParser = $propertiesParser;
        $this->useParser = $useParser;
    }

    public function parse(Class_ $node, Entity\FQCN $fqcn, $file){
        $classData = new Entity\ClassData($fqcn, $file);
        if($node->extends){
            $classData->setParentClass(
                $this->useParser->getFQCN($node->extends)->toString()
            );
        }
        foreach($node->implements AS $interfaceName){
            $classData->addInterface(
                $this->useParser->getFQCN($interfaceName)->toString()
            );
        }
        $classData->startLine = $node->getAttribute("startLine");
        $classData->doc = $this->parseDocComments(
            $classData,
            $node->getAttribute("comments")
        );
        foreach($node->stmts AS $child){
            if($child instanceof ClassMethod){
                $classData->addMethod($this->parseMethod($child));
            }
            elseif($child instanceof Property){
                foreach($child->props AS $prop){
                    $classData->addProp(
                        $this->parseProperty($prop, $child->type)
                    );
                }
            }
            elseif($child instanceof ClassConst){
                foreach($child->consts AS $const){
                    $classData->addConst($const->name);
                }
            }
        }
        return $classData;
    }

    /**
     * Parses Method node though $methodsParser
     *
     * @return MethodData
     */
    protected function parseMethod(ClassMethod $node)
    {
        return $this->methodsParser->parse($node);
    }

    /**
     * Parses Property node through $propertiesParser
     *
     * @return ClassProperty
     */
    protected function parseProperty(PropertyProperty $node, $modifier)
    {
        return $this->propertiesParser->parse($node, $modifier);
    }


    /**
     * Parses doc comments trough $docParser
     */
    protected function parseDocComments($classData, $node)
    {
        $classData->doc = $this->docParser->parse($node);
    }

}
