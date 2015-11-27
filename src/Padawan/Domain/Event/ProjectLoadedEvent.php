<?php

namespace Padawan\Domain\Event;

use Symfony\Component\EventDispatcher\Event;

class ProjectLoadedEvent extends Event
{
    public function __construct($project)
    {
        $this->project = $project;
    }

    public $project;
}
