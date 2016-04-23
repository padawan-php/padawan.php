<?php

namespace Padawan\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Amp;

class KillCommand extends AsyncCommand
{
    protected function configure()
    {
        $this->setName("kill")
            ->setDescription("Stops padawan server");
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        yield $output->write(json_encode([]));
        yield $output->disconnect();
        printf("Goodbye\n");
        yield Amp\stop();
    }
}
