<?php

namespace Domain\Generator;

use Domain\Core\Completion\Scope\FileScope;
use Domain\Core\Index;
use Domain\Core\Project;

interface IndexGenerator
{
    public function generateIndex(Project $project);

    public function generateProjectIndex(Project $project);

    public function processFile(Index $index, $file, $rewrite = false, $createCache = true);

    public function createScopeForFile($file, $createCache = true);

    public function processFileScope(Index $index, FileScope $scope);
}
