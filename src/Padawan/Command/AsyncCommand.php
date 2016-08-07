<?php

namespace Padawan\Command;

use DI\Container;
use Padawan\Framework\Application\Socket;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Padawan\Framework\Application\Socket\HttpOutput;
use Symfony\Component\Console\Exception\ExceptionInterface;

abstract class AsyncCommand extends Command
{

    /**
     * @return \Generator
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // force the creation of the synopsis before the merge with the app definition
        $this->getSynopsis(true);
        $this->getSynopsis(false);

        // add the application arguments and options
        $this->mergeApplicationDefinition();

        // bind the input against the command specific arguments/options
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors()) {
                throw $e;
            }
        }

        $this->initialize($input, $output);

        // The command name argument is often omitted when a command is executed directly with its run() method.
        // It would fail the validation if we didn't make sure the command argument is present,
        // since it's required by the application.
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }

        $input->validate();

        return $this->execute($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof HttpOutput) {
            return $this->executeAsync($input, $output);
        }
    }

    /**
     * @return \Generator
     */
    abstract protected function executeAsync(InputInterface $input, HttpOutput $output);

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

    /**
     * @return Socket
     */
    public function getApplication()
    {
        return parent::getApplication();
    }
}
