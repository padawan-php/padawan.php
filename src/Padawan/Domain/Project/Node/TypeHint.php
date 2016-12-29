<?php

namespace Padawan\Domain\Project\Node;

class TypeHint extends Variable {
    public $startLine = 0;

    public static function create(Variable $variable, $startLine)
    {
        $th = new TypeHint($variable->getName());
        $th->setType($variable->getType());
        $th->startLine = $startLine;

        return $th;
    }
}
