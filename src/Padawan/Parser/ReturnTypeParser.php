<?php
namespace Padawan\Parser;

use Padawan\Domain\Project\FQCN;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

class ReturnTypeParser extends ParamParser
{
    /**
     * @param  ClassMethod|Function_ $node
     * @return FQCN
     */
    public function parse($node)
    {
        return $this->createFQCN($node->returnType, 0);
    }
}
