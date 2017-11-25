<?php

namespace Padawan\Domain\Project;

/**
 * Class Config
 */
class Config
{
    public function __construct(
        $phpVersion,
        $plugins,
        $excludedDirs,
        $cacheDir
    ) {
        $this->_phpVersion = $phpVersion;
        $this->_plugins = $plugins;
        $this->_excludedDirs = $excludedDirs;
        $this->_cacheDir = $cacheDir;
    }

    /**
     * @return Config
     */
    public static function default()
    {
        return new static(
            "5.6",
            [],
            [],
            ".padawan"
        );
    }

    public function phpVersion()
    {
        return $this->_phpVersion;
    }

    public function plugins()
    {
        return $this->_plugins;
    }

    public function excludedDirs()
    {
        return $this->_excludedDirs;
    }

    public function cacheDir()
    {
        return $this->_cacheDir;
    }

    private $_phpVersion;
    private $_plugins;
    private $_excludedDirs;
    private $_cacheDir;
}
