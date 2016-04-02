<?php

namespace Padawan\Domain\Event;

use Padawan\Domain\Core\Completion\Context;
use Padawan\Domain\Core\Project;
use Symfony\Component\EventDispatcher\Event;

class CustomCompleterEvent extends Event
{
    /** @var CompleterInterface */
    public $completer = null;
    /** @var Context */
    public $context;
    /** @var Project */
    public $project;
    public function __construct(Project $project, Context $context)
    {
        $this->context = $context;
        $this->project = $project;
    }
}
