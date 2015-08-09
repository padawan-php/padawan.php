<?php

namespace Application\HTTP;

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
        } elseif ($commandName == 'complete') {
            $command = new \Command\CompleteCommand($container);
        } elseif ($commandName == 'save') {
            $command = new \Command\SaveCommand($container);
        } else {
            $command = new \Command\ErrorCommand($container);
        }
        return $command;
    }
}
