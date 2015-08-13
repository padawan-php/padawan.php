<?php

namespace Command;

class KillCommand implements CommandInterface
{
    public function execute()
    {
        die();
    }
}
