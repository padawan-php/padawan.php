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
            return new Project(new Index, $path);
        }
    }

    private function loadCoreIndex()
    {
        if (self::$coreIndex) {
            return;
        }
        self::$coreIndex = $this->read(STUBS_DIR)->getIndex();
        $indexClass = new \ReflectionClass(Index::class);
        $coreIndexProperty = $indexClass->getProperty("coreIndex");
        $coreIndexProperty->setAccessible(true);
        $coreIndexProperty->setValue(self::$coreIndex);
    }

    private static $coreIndex;
    private $pool;
    private $persister;
}
