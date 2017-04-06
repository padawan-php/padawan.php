<?php

namespace Padawan\Parser;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Context;
use Padawan\Domain\Project\Node\Comment;
use Padawan\Domain\Project\Node\MethodParam;
use Padawan\Domain\Project\Node\Variable;
use Padawan\Domain\Project\Node\ClassProperty;

class CommentParser
{
    public function __construct(UseParser $useParser) {
        $this->useParser = $useParser;
    }

    /**
     * Parses DocComment block
     *
     * @param string $doc
     * @return Comment
     */
    public function parse($doc) {
        $text = $doc;
        if (is_array($doc)) {
            $doc = array_shift($doc);
            $text = $doc->getText();
        }
        $comment = new Comment(
            $this->trimComment($text)
        );
        $this->parseDoc($comment, $text);

        return $comment;
    }

    /**
     * Parses doc comment and populates comment entity
     *
     * @param string $text
     */
    protected function parseDoc(Comment $comment, $text) {
        $context = $this->getContext();
        try {
            $block = new DocBlock($text, $context);
            foreach ($block->getTags() AS $tag) {
                switch ($tag->getName()) {
                case "param":
                    $comment->addVar(
                        $this->createMethodParam($tag)
                    );
                    break;
                case "var":
                    $comment->addVar(
                        $this->createVar($tag)
                    );
                    break;
                case "return":
                    $comment->setReturn(
                        $this->getFQCN($tag->getType())
                    );
                    break;
                case "property":
                case "property-read":
                case "property-write":
                    $comment->addProperty(
                        $this->createProperty($tag)
                    );
                    break;
                case "inheritdoc":
                    $comment->markInheritDoc();
                    break;
                }
            }
        }
        catch (\Exception $e) {

        }
    }
    protected function createMethodParam(Tag $tag) {
        $name = trim($tag->getVariableName(), '$');
        $param = new MethodParam($name);
        $param->setType($this->getFQCN($tag->getType()));
        return $param;
    }
    protected function createVar(Tag $tag) {
        $name = trim($tag->getVariableName(), '$');
        $param = new Variable($name);
        $type = $tag->getType();
        if (empty($type)) {
            // fallback to desecription to support docs like:
            // /** @var $varaname Type */
            $type = $tag->getDescription();
        }
        $param->setType($this->getFQCN($type));
        return $param;
    }
    protected function createProperty(Tag $tag) {
        $name = trim($tag->getVariableName(), '$');
        $prop = new ClassProperty;
        $prop->name = $name;
        $prop->setType($this->getFQCN($tag->getType()));
        return $prop;
    }

    /**
     * Creates FQN by type string
     *
     * @param string $type
     * @return \Padawan\Domain\Project\FQCN
     */
    protected function getFQCN($type) {
        return $this->useParser->parseType($type);
    }

    /**
     * @return string
     */
    protected function trimComment($comment) {
        $lines = explode("\n", $comment);
        foreach ($lines AS $key => $line) {
            $lines[$key] = preg_replace([
                "/^\/\**/",
                "/^ *\* */",
                "/\**\/$/"
            ], "", $line);
        }
        $text = implode("\n", $lines);
        return utf8_encode($text);
    }

    /**
     * @return Context
     */
    protected function getContext() {
        $uses = $this->useParser->getUses();
        $namespace = $uses->getFQCN()->toString();
        $aliases = array_map(function($fqcn) {
            return $fqcn->toString();
        }, $uses->all());
        return new Context($namespace, $aliases);
    }

    /** @property UseParser */
    private $useParser;
}
