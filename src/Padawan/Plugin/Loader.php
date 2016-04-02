<?php

namespace Padawan\Plugin;

use DI\Container;

class Loader
{
    public function __construct(Package $package, Container $container)
    {
        $this->package = $package;
        $this->container = $container;
    }
    public function load()
    {
        $plugins = $this->package->getPluginsList();
        foreach ($plugins as $pluginName) {
            $this->loadPlugin($pluginName);
        }
    }
    public function loadPlugin($pluginName)
    {
        $parts = explode("/", $pluginName);
        $className = implode("\\", array_map(function($part) {
            return implode("", array_map(
                function($part) {
                    return ucfirst($part);
                },
                explode("-", $part)
            ));
        }, $parts));
        $className .= "\\" . $this->pluginClassName;
        try {
            /** @var \Padawan\Plugin\PluginInterface */
            $plugin = $this->container->get($className);
            $plugin->init();
        } catch (\Exception $e) {
            printf("Plugin Error: %s\n", $e->getMessage());
        }
    }

    private $pluginClassName = "Plugin";
    /** @var Package */
    private $package;
    /** @var Container */
    private $container;
}
