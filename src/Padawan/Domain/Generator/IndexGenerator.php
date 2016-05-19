<?php

namespace Padawan\Domain\Generator;

use Padawan\Domain\Project;
use Padawan\Domain\Project\File;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Scope\FileScope;

interface IndexGenerator
{
    public function generateIndex(Project $project);

    public function generateProjectIndex(Project $project, $rewrite = true);

    public function processFile(Index $index, $filePath, $rewrite = true);

    public function createScopeForFile(File $file, $content, Index $index, $rewrite = true);

    public function processFileScope(File $file, Index $index, FileScope $scope);
}
