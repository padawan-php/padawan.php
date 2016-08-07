<?php

namespace Fake;

use Padawan\Framework\Application\Socket\HttpOutput;

/**
 * Class Output
 */
class Output extends HttpOutput
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
