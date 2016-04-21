<?php

namespace Fake;

use Padawan\Framework\Application\Socket\SocketOutput;

/**
 * Class Output
 */
class Output extends SocketOutput
{
    public $output = [];

    public function __construct()
    {
    }

    public function write($message, $newline = false, $options = 0)
    {
        $this->output[] = $message;
    }

    public function writeln($message, $options = 0)
    {
        $this->output[] = $message;
    }

    public function disconnect()
    {
    }
}
