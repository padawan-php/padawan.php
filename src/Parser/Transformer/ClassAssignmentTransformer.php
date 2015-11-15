<?php

namespace Parser\Transformer;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use Domain\Core\Node\ClassData;
use Domain\Core\Node\ClassProperty;
use Framework\Complete\Resolver\NodeTypeResolver;
use PhpParser\Node\Expr\Variable;
use Domain\Core\FQCN;

class ClassAssignmentTransformer
{
    public function __construct(NodeTypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }
    public function transform(Assign $node, ClassData $class, $scope, $index)
    {
        $fetch = $node->var;
        if (!$fetch instanceof PropertyFetch) {
            return;
        }
        if (!$fetch->var instanceof Variable || $fetch->var->name !== 'this') {
            return;
        }
        $type = $this->typeResolver->getType(
            $node->expr,
            $index,
            $scope
        );
        if ($class->hasProp($fetch->name)) {
            $prop = $class->getProp($fetch->name);
            if ($type instanceof FQCN) {
                $prop->setType($type);
            }
        } else {
            $class->addProp(
                new ClassProperty($fetch->name, $type)
            );
        }
    }

    private $typeResolver;
}
