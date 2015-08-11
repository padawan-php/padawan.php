<?php

namespace Application\HTTP;

use Symfony\Component\EventDispatcher\Event;

class ProjectLoadEvent extends Event
{
    public function __construct($project)
    {
        $this->project = $project;
    }

    public $project;
}
