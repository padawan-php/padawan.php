<?php

namespace Padawan\Parser\Transformer;

use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Param;
use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Project\Node\MethodParam;
use Padawan\Parser\CommentParser;
use Padawan\Parser\ParamParser;
use Padawan\Parser\UseParser;
use Padawan\Parser\InlineDocBlockParser;

class FunctionTransformer
{
    public function __construct(
        CommentParser $commentParser,
        ParamParser $paramParser,
        UseParser $useParser,
        InlineDocBlockParser $inlineDocBlockParser
    ) {
        $this->commentParser        = $commentParser;
        $this->paramParser          = $paramParser;
        $this->useParser            = $useParser;
        $this->inlineDocBlockParser = $inlineDocBlockParser;
    }
    public function tranform(Function_ $node)
    {
        $function = new FunctionData($node->name);
        $function->startLine = $node->getAttribute("startLine");
        $function->endLine = $node->getAttribute("endLine");
        $this->parseComments($function, $node->getAttribute("comments"));
        foreach ($node->params AS $child) {
            if ($child instanceof Param) {
                $function->addArgument($this->tranformArgument($child));
            }
        }
        $variables = $this->inlineDocBlockParser->parse($node);
        foreach ($variables as $variable) {
            $function->addVar($variable);
        }
        return $function;
    }
    protected function parseComments(FunctionData $function, $comments)
    {
        if (is_array($comments)) {
            /** @var Comment */
            $comment = $this->commentParser->parse(
                $comments[count($comments) - 1]->getText()
            );
            if ($comment->isInheritDoc()) {
                $function->doc = Comment::INHERIT_MARK;
            } else {
                $function->doc = $comment->getDoc();
                $function->return = $comment->getReturn();
                foreach ($comment->getVars() as $var) {
                    if ($var instanceof MethodParam) {
                        $function->addParam($var);
                    }
                }
            }
        }
    }
    protected function tranformArgument(Param $node)
    {
        return $this->paramParser->parse($node);
    }

    private $paramParser;
    private $commentParser;
    private $useParser;
    private $inlineDocBlockParser;
}
