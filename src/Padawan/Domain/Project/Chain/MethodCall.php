<?php

namespace Padawan\Domain\Project\Chain;

use Padawan\Domain\Project\Chain;

class MethodCall extends Chain
{
    public function __construct(Chain $child = null, $name = "", array $args = [])
    {
        parent::__construct($child, $name, 'method');
        $this->args = $args;
    }

    public function getArgs()
    {
        return $this->args;
    }

    private $args;
}
