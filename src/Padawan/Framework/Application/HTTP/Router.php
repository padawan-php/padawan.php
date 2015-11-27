<?php

namespace Padawan\Framework\Application\HTTP;

use Padawan\Framework\Application\RouterInterface;
use DI\Container;

class Router implements RouterInterface
{

    /**
     * Finds command by its name
     *
     * @param $commandName string
     * @param $container Container
     * @return \Padawan\Command\CommandInterface
     */
    public function getCommand($commandName, Container $container)
    {
        if ($commandName == 'generate') {
            $command = new \Padawan\Command\GenerateCommand($container);
        } elseif ($commandName == 'complete') {
            $command = new \Padawan\Command\CompleteCommand($container);
        } elseif ($commandName == 'save') {
            $command = new \Padawan\Command\SaveCommand($container);
        } elseif ($commandName == 'kill') {
            $command = new \Padawan\Command\KillCommand();
        } else {
            $command = new \Padawan\Command\ErrorCommand();
        }
        return $command;
    }
}
