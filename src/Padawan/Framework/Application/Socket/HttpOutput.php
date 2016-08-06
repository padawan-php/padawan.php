<?php

namespace Padawan\Framework\Application\Socket;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use React\Http\Response;
use React\Promise;

/**
 * Class HttpOutput
 */
class HttpOutput extends NullOutput
{
    public function __construct(Response $client)
    {
        $this->client = $client;
    }

    public function defaultHeaders()
    {
        $this->writeHead(200, [
            'Content-Type' => 'application/json'
        ]);
    }

    public function writeHead($status, $headers = [])
    {
        $this->isHeadWritten = true;
        return $this->client->writeHead($status, $headers);
    }

    public function write($message, $newline = false, $options = 0)
    {
        if (!$this->isHeadWritten) {
            $this->defaultHeaders();
        }
        if (is_array($message)) {
            $message = implode("\n", $message);
        }
        $this->client->end($message);
        return Promise\resolve();
    }

    public function writeln($message, $options = 0)
    {
        if (!$this->isHeadWritten) {
            $this->defaultHeaders();
        }
        return $this->client->write($message . "\n");
    }

    public function disconnect()
    {
        if (!$this->isHeadWritten) {
            $this->defaultHeaders();
        }
        return Promise\resolve()->then(function() {
            $this->client->end();
        });
    }

    private $client;
    private $isHeadWritten = false;
}
