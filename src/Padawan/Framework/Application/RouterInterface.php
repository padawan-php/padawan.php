<?php

namespace Padawan\Framework\Application;

use DI\Container;

interface RouterInterface
{
    /**
     * @return \Padawan\Command\CommandInterface
     */
    public function getCommand($commandName, Container $container);
}
