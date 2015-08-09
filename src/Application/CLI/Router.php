<?php

namespace Application\CLI;

use Application\RouterInterface;
use DI\Container;

class Router implements RouterInterface
{

    /**
     * Finds command by its name
     *
     * @param $commandName string
     * @param $container Container
     * @return \Command\CommandInterface
     */
    public function getCommand($commandName, Container $container)
    {
        if ($commandName == 'generate') {
            $command = new \Command\GenerateCommand($container);
        } elseif ($commandName == 'save') {
            $command = new \Command\SaveCommand($container);
        } elseif ($commandName == 'plugin') {
            $command = new \Command\PluginCommand($container);
        } else {
            $command = new \Command\ErrorCommand();
        }
        return $command;
    }
}
