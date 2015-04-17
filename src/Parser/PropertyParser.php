<?php

namespace Parser;

use Entity\Node\ClassProperty;
use PhpParser\Node\Stmt\PropertyProperty as Property;

class PropertyParser{
    /**
     * Parses Property node to ClassProperty
     *
     * @param $node Property
     *
     * @return ClassProperty
     */
    public function parse(Property $node, $modifier=0)
    {
        $prop = new ClassProperty;
        $prop->name = $node->name;
        $prop->setModifier($modifier);
        return $prop;
    }
}
