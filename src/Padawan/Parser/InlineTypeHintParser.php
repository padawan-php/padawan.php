<?php

namespace Padawan\Parser;

use Padawan\Domain\Project\Node\TypeHint;
use Padawan\Domain\Project\Node\Comment;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Expr\FuncCall;
use Psr\Log\LoggerInterface;

class InlineTypeHintParser {

    /**
     * Constructs
     *
     */
    public function __construct(
        LoggerInterface $logger,
        CommentParser $commentParser
    )
    {
        $this->logger        = $logger;
        $this->commentParser = $commentParser;
    }

    /**
     * Parses inline type hint
     *
     * @return TypeHint[]
     */
    public function parse($node)
    {
        $result = [];

        if (empty($node->stmts)) {
            return $result;
        }
        foreach ($node->stmts AS $stmt) {
            if (!empty($stmt->stmts)) {
                $result = array_merge($result, $this->parse($stmt));
            }
            $comments = $stmt->getAttribute('comments');
            if (empty($comments)) {
                continue;
            }
            foreach ($comments as $comment) {
                $text = trim($comment->getText());
                if (!empty($text)) {
                    if (strpos($text, '/** @var ') !== 0) {
                        // only parse inline type hint
                        continue;
                    }
                    $comment = $this->commentParser->parse($text);
                    foreach ($comment->getVars() as $variable) {
                        $result[] = TypeHint::create(
                            $variable, $stmt->getAttribute('startLine')
                        );
                    }
                }
            }
        }

        return $result;
    }

    /** @property CommentParser $commentParser */
    private $commentParser;
    /** @property LoggerInterface */
    private $logger;
}
