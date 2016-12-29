<?php

namespace Padawan\Parser;

use Padawan\Domain\Project;
use Padawan\Domain\Project\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Name;

class ClassParser
{
    private $docParser;
    private $methodsParser;
    private $propertiesParser;
    private $useParser;

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

    public function parse(Class_ $node, Project\FQN $fqn, $file) {
        $fqcn = new Project\FQCN($node->name, $fqn);
        $classData = new Node\ClassData($fqcn, $file);
        if ($node->extends instanceof Name) {
            $classData->setParent(
                $this->useParser->getFQCN($node->extends)
            );
        }
        foreach ($node->implements AS $interfaceName) {
            $classData->addInterface(
                $this->useParser->getFQCN($interfaceName)
            );
        }
        $classData->startLine = $node->getAttribute("startLine");
        $this->parseDocComments(
            $classData,
            $node->getAttribute("comments")
        );
        foreach ($node->stmts AS $child) {
            if ($child instanceof ClassMethod) {
                $classData->addMethod($this->parseMethod($child));
            }
            elseif ($child instanceof Property) {
                foreach ($child->props AS $prop) {
                    $classData->addProp(
                        $this->parseProperty($prop, $child->type, $child->getAttribute('comments'))
                    );
                }
            }
            elseif ($child instanceof ClassConst) {
                foreach ($child->consts AS $const) {
                    $classData->addConst($const->name);
                }
            }
        }
        return $classData;
    }

    /**
     * Parses Method node though $methodsParser
     *
     * @return Node\MethodData
     */
    protected function parseMethod(ClassMethod $node)
    {
        return $this->methodsParser->parse($node);
    }

    /**
     * Parses Property node through $propertiesParser
     *
     * @return Node\ClassProperty
     */
    protected function parseProperty(PropertyProperty $node, $modifier, $comments)
    {
        return $this->propertiesParser->parse($node, $modifier, $comments);
    }


    /**
     * Parses doc comments trough $docParser
     *
     * @param Node\ClassData $classData
     */
    protected function parseDocComments($classData, $node)
    {
        /** @var Node\Comment $comment */
        $comment = $this->docParser->parse($node);
        $classData->doc = $comment->getDoc();
        foreach ($comment->getProperties() as $prop) {
            $classData->addProp($prop);
        }
    }
}
