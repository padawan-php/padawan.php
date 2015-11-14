<?php

namespace Domain\Event;

use Domain\Core\FQCN;
use Domain\Core\Chain;
use Symfony\Component\EventDispatcher\Event;

class TypeResolveEvent extends Event
{
    public function __construct(Chain $chain = null, $type = null)
    {
        $this->chain = $chain;
        $this->type = $type;
    }

    /**
     * @return FQCN
     */
    public function getType()
    {
        return $this->type;
    }
    public function setType($fqcn = null)
    {
        $this->type = $fqcn;
    }

    /**
     * @return Chain
     */
    public function getChain()
    {
        return $this->chain;
    }

    private $chain;
    private $type;
}
