<?php

namespace Padawan\Command;


use Symfony\Component\Console\Input\InputInterface;
use Padawan\Framework\Application\Socket\SocketOutput;
use Amp;

class KillCommand extends AsyncCommand
{
    protected function configure()
    {
        $this->setName("kill")
            ->setDescription("Stops padawan server");
    }
    protected function executeAsync(InputInterface $input, SocketOutput $output)
    {
        yield $output->write(json_encode([]));
        yield $output->disconnect();
        printf("Goodbye\n");
        yield Amp\stop();
    }
}
