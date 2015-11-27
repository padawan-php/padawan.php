<?php

namespace Padawan\Plugin;

use Padawan\Framework\Utils\PathResolver;

class Package
{
    public function __construct(PathResolver $path)
    {
        $this->path = $path;
    }
    public function getPluginsList()
    {
        $plugins = [];
        if ($this->path->exists($this->getPackageFile())) {
            $contents = $this->path->read($this->getPackageFile());
            $contents = json_decode($contents, true);
            foreach ($contents as $plugin) {
                $plugins[$plugin] = $plugin;
            }
        }
        return $plugins;
    }
    public function writePluginsList(array $plugins)
    {
        $contents = [];
        foreach ($plugins as $plugin) {
            $contents[] = $plugin;
        }
        $this->path->write($this->getPackageFile(), json_encode($contents));
    }
    protected function getPackageFile()
    {
        $path = $this->path->join([__DIR__, '..', '..', $this->packageName]);
        return $path;
    }
    private $packageName = 'plugins.json';
    /** @var PathResolver */
    private $path;
}
