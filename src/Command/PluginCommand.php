<?php

namespace Command;

class PluginCommand extends AbstractCommand
{
    public function run(array $arguments = [])
    {
        $commandName = array_shift($arguments);
        $pluginName = array_shift($arguments);
        if ($commandName === 'add') {
            return $this->addAction($pluginName);
        } elseif ($commandName === 'remove') {
            return $this->removeAction($pluginName);
        }
    }
    public function addAction($pluginName)
    {
        /** @var \Plugin\Package */
        $package = $this->getContainer()->get("Plugin\\Package");
        $plugins = $package->getPluginsList();
        if (array_key_exists($pluginName, $plugins)) {
            return;
        }
        $plugins[] = $pluginName;
        $package->writePluginsList($plugins);
    }
    public function removeAction($pluginName)
    {
        /** @var \Plugin\Package */
        $package = $this->getContainer()->get("Plugin\\Package");
        $plugins = $package->getPluginsList();
        if (!array_key_exists($pluginName, $plugins)) {
            return;
        }
        unset($plugins[$pluginName]);
        $package->writePluginsList($plugins);
    }
}
