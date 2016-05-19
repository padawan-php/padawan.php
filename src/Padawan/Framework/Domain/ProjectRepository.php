<?php

namespace Padawan\Framework\Domain;


use Padawan\Domain\Project;
use Padawan\Framework\IO\Reader;
use Padawan\Framework\Domain\Project\Persister;
use Padawan\Framework\Domain\Project\InMemoryIndex;
use Padawan\Domain\ProjectRepository as RepositoryInterface;

/**
 * Class Repository
 */
class ProjectRepository implements RepositoryInterface
{
    public function __construct(Persister $persister)
    {
        $this->persister = $persister;
        $this->pool = [];
        $this->loadCoreIndex();
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
        $project = $this->persister->load($path);
        if (!empty($project)) {
            return $project;
        } else {
            return new Project(new InMemoryIndex, $path);
        }
    }

    private function loadCoreIndex()
    {
        if (self::$coreIndex) {
            return;
        }
        self::$coreIndex = $this->read(STUBS_DIR)->getIndex();
        $indexClass = new \ReflectionClass(InMemoryIndex::class);
        $coreIndexProperty = $indexClass->getProperty("coreIndex");
        $coreIndexProperty->setAccessible(true);
        $coreIndexProperty->setValue(self::$coreIndex);
    }

    private static $coreIndex;
    private $pool;
    private $persister;
}
