<?php

namespace Padawan\Framework\Project;


use Padawan\Framework\IO\Reader;
use Padawan\Domain\ProjectRepository;
use Padawan\Domain\Core\Project;
use Padawan\Domain\Core\Index;

/**
 * Class Repository
 */
class Repository implements ProjectRepository
{
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->pool = [];
    }
    public function findByPath($path)
    {
        if (!array_key_exists($path, $this->pool)) {
            $this->pool[$path] = $this->read($path);
        }
        return $this->pool[$path];
    }

    private function read($path)
    {
        $project = $this->reader->read($path);
        if (!empty($project)) {
            return $project;
        } else {
            return new Project(new Index, $path);
        }
    }
    private $pool;
    private $reader;
}
