<?php

namespace Generator;

use Symfony\Component\EventDispatcher\Event;
use Entity\Project;

class IndexGenerationEvent extends Event
{
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /** @return Project */
    public function getProject()
    {
        return $this->project;
    }

    /** @var Project */
    private $project;
}
