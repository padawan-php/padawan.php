<?php

namespace Padawan\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Padawan\Plugin\Package;

class PluginCommand extends CliCommand
{
    protected function configure()
    {
        $this->setName("plugin")
            ->setDescription("Manages plugins for your project")
            ->addArgument(
                "command_name",
                InputArgument::REQUIRED,
                "Command to execute. Can be add or remove"
            )->addArgument(
                "plugin_name",
                InputArgument::REQUIRED,
                "Plugin to work with"
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandName = $input->getArgument("command_name");
        $pluginName = $input->getArgument("plugin_name");
        /** @var \Padawan\Plugin\Package */
        $package = $this->getContainer()->get(Package::class);
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
