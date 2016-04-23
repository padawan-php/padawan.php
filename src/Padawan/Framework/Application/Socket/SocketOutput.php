<?php

namespace Padawan\Framework\Application\Socket;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Amp\Socket\Client;

/**
 * Class SocketOutput
 */
class SocketOutput extends NullOutput
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function write($message, $newline = false, $options = 0)
    {
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        return $this->client->write($message);
    }

    public function writeln($message, $options = 0)
    {
        return $this->client->write($message . "\n");
    }

    public function disconnect()
    {
        return $this->client->close();
    }

    private $client;
}
