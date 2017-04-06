<?php

namespace Padawan\Parser;

use Padawan\Domain\Project\Node\MethodData;
use Padawan\Domain\Project\Node\MethodParam;
use Padawan\Domain\Project\Node\Comment;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Param;
use PhpParser\Node\Name;

class MethodParser {

    /**
     * Constructs
     *
     * @param UseParser $useParser
     */
    public function __construct(
        UseParser $useParser,
        CommentParser $commentParser,
        ParamParser $paramParser,
        InlineDocBlockParser $inlineDocBlockParser
    )
    {
        $this->useParser            = $useParser;
        $this->commentParser        = $commentParser;
        $this->paramParser          = $paramParser;
        $this->inlineDocBlockParser = $inlineDocBlockParser;
    }

    /**
     * Parses ClassMethod node to MethodData
     *
     * @return MethodData
     */
    public function parse(ClassMethod $node)
    {
        $method = new MethodData($node->name);
        $method->startLine = $node->getAttribute("startLine");
        $method->endLine = $node->getAttribute("endLine");
        $method->setType($node->type);
        $comments = $node->getAttribute("comments");
        if (is_array($comments)) {
            /** @var Comment */
            $comment = $this->commentParser->parse(
                $comments[count($comments) - 1]->getText()
            );
            if ($comment->isInheritDoc()) {
                $method->doc = Comment::INHERIT_MARK;
            } else {
                $method->doc = $comment->getDoc();
                $method->return = $comment->getReturn();
                foreach ($comment->getVars() as $var) {
                    if ($var instanceof MethodParam) {
                        $method->addParam($var);
                    }
                }
            }
        }
        foreach ($node->params AS $child) {
            if ($child instanceof Param) {
                $method->addParam($this->parseMethodArgument($child));
            }
        }
        $variables = $this->inlineDocBlockParser->parse($node);
        foreach ($variables as $variable) {
            $method->addVar($variable);
        }
        return $method;
    }
    protected function parseMethodArgument(Param $node) {
        return $this->paramParser->parse($node);
    }

    /** @var UseParser $useParser */
    private $useParser;
    /** @property CommentParser $commentParser */
    private $commentParser;
    /** @var ParamParser */
    private $paramParser;
    /** @var InlineDocBlockParser */
    private $inlineDocBlockParser;
}
