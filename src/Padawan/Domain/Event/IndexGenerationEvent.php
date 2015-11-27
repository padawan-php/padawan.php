<?php

namespace Padawan\Domain\Event;

use Symfony\Component\EventDispatcher\Event;
use Padawan\Domain\Core\Project;

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
