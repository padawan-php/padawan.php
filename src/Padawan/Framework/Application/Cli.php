<?php

namespace Padawan\Framework\Application;

use Padawan\Framework\Application;
use Padawan\Command\GenerateCommand;

class Cli extends Application
{
    public function __construct()
    {
        parent::__construct("Padawan CLI");
        $this->loadCommands();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function loadCommands()
    {
        $this->add(new GenerateCommand);
    }
}
