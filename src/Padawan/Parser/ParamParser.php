<?php

namespace Padawan\Parser;

use PhpParser\Node\Param;
use PhpParser\Node\Name;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Node\MethodParam;

class ParamParser {
    public function __construct(UseParser $useParser) {
        $this->useParser = $useParser;
    }
    /**
     * @param  Param       $node
     * @return MethodParam
     */
    public function parse($node) {
        $param = new MethodParam($node->name);
        $param->setFQCN($this->createFQCN($node->type, (int)$node->variadic));
        return $param;
    }
    /**
     * @param  null|Name|NullableType|Identifier|string $type
     * @param  int                                      $dimension
     * @return null|FQCN
     */
    protected function createFQCN($type, $dimension) {
        do {
            if ($type instanceof NullableType) {
                $type = $type->type;
            }
            if ($type instanceof Identifier) {
                $type = $type->name;
            }
            if (!$type) {
                return null;
            }
            if (is_string($type)) {
                return new FQCN($type, '', $dimension);
            }
        } while (!$type instanceof Name);

        $fqcn = $this->useParser->getFQCN($type);
        return new FQCN($fqcn->className, $fqcn->namespace, $dimension);
    }

    private $useParser;
}
