<?php

namespace Padawan\Domain\Core;

class Project
{
    private $index;
    private $rootFolder;
    private $plugins = [];

    public function __construct(Index $index, $rootFolder = "")
    {
        $this->index        = $index;
        $this->rootFolder   = $rootFolder;
    }
    public function getRootFolder()
    {
        return $this->rootFolder;
    }
    public function getRootDir()
    {
        return $this->getRootFolder();
    }

    /**
     * Returns project's index
     * @return Index
     */
    public function getIndex()
    {
        return $this->index;
    }
    public function setIndex(Index $index)
    {
        $this->index = $index;
    }
    public function getPlugins()
    {
        return $this->plugins;
    }
    public function addPlugin($key, $plugin)
    {
        $this->plugins[$key] = $plugin;
    }
    public function getPlugin($key)
    {
        if (array_key_exists($key, $this->plugins)) {
            return $this->plugins[$key];
        }
        return [];
    }
}
