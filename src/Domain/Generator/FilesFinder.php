<?php

namespace Domain\Generator;

use Domain\Core\Project;

interface FilesFinder
{
    public function findProjectFiles(Project $project);

    public function findChangedProjectFiles(Project $project);
}
