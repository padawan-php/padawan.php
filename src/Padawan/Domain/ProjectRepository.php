<?php

namespace Padawan\Domain;

use Padawan\Domain\Core\Project;

/**
 * Interface ProjectRepository
 */
interface ProjectRepository
{
    /**
     * loads a project by path
     *
     * @param string $path
     * @return Project
     */
    public function findByPath($path);
}
