<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Symfony\Component\EventDispatcher\Event;

class CustomCompleterEvent extends Event
{
    /** @var CompleterInterface */
    public $completer = null;
    /** @var Context */
    public $context;
    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
