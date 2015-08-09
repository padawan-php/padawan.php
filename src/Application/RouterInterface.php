<?php

namespace Application;

use DI\Container;

interface RouterInterface
{
    /**
     * @return \Command\CommandInterface
     */
    public function getCommand($commandName, Container $container);
}
