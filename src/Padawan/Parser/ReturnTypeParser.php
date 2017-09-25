<?php
namespace Padawan\Parser;

use PhpParser\Node\Stmt\ClassMethod;

class ReturnTypeParser extends ParamParser
{
    /**
     * @param  ClassMethod $node
     * @return FQCN
     */
    public function parse($node)
    {
        return $this->createFQCN($node->returnType, false);
    }
}
