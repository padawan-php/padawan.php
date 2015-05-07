<?php

namespace Parser;

use Entity;
use Entity\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Name;

/**
 * SomeComment
 */
class ClassParser{
    /**
     * @property ClassParser
     */
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

    public function parse(Class_ $node, Entity\FQN $fqn, $file){
        $fqcn = new Entity\FQCN($node->name, $fqn);
        $classData = new Node\ClassData($fqcn, $file);
        if($node->extends instanceof Name){
            $classData->setParent(
                $this->useParser->getFQCN($node->extends)
            );
        }
        foreach($node->implements AS $interfaceName){
            $classData->addInterface(
                $this->useParser->getFQCN($interfaceName)
            );
        }
        $classData->startLine = $node->getAttribute("startLine");
        $this->parseDocComments(
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
                        $this->parseProperty($prop, $child->type, $child->getAttribute('comments'))
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
    protected function parseProperty(PropertyProperty $node, $modifier, $comments)
    {
        return $this->propertiesParser->parse($node, $modifier, $comments);
    }


    /**
     * Parses doc comments trough $docParser
     *
     * @param \Entity\Node\ClassData $classData
     */
    protected function parseDocComments($classData, $node)
    {
        /** @var \Entity\Node\Comment $comment */
        $comment = $this->docParser->parse($node);
        $classData->doc = $comment->getDoc();
    }
}
