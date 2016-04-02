<?php

namespace Padawan\Domain\Generator;

use Padawan\Domain\Core\Project;

interface FilesFinder
{
    public function findProjectFiles(Project $project);

    public function findChangedProjectFiles(Project $project);
}
