<?php

namespace Command;

class PluginCommand extends AbstractCommand
{
    public function run(array $arguments = [])
    {
        $commandName = array_shift($arguments);
        $pluginName = array_shift($arguments);
        /** @var \Plugin\Package */
        $package = $this->getContainer()->get("Plugin\\Package");
        $plugins = $package->getPluginsList();
        if ($commandName === 'add') {
            if (array_key_exists($pluginName, $plugins)) {
                return;
            }
            $plugins[] = $pluginName;
        } elseif ($commandName === 'remove') {
            if (!array_key_exists($pluginName, $plugins)) {
                return;
            }
            unset($plugins[$pluginName]);
        }
        $package->writePluginsList($plugins);
    }
}
