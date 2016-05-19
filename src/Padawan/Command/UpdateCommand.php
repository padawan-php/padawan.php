<?php

namespace Padawan\Command;

use Padawan\Domain\Project;
use Padawan\Domain\ProjectRepository;
use Padawan\Domain\Generator\IndexGenerator;
use Padawan\Framework\Domain\Project\Persister;
use Padawan\Framework\Domain\Project\InMemoryIndex;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Padawan\Framework\Application\Socket\SocketOutput;

class UpdateCommand extends AsyncCommand
{
    protected function configure()
    {
        $this->setName("update")
            ->setDescription("Updates index for the project")
            ->addArgument(
                "path",
                InputArgument::REQUIRED,
                "Path to the project root. Default: current directory"
            );
    }
    protected function executeAsync(InputInterface $input, SocketOutput $output)
    {
        $path = $input->getArgument("path");

        $projectRepository = $this->getContainer()->get(ProjectRepository::class);
        $project = $projectRepository->findByPath($path);
        $generator = $this->getContainer()->get(IndexGenerator::class);

        $generator->generateProjectIndex($project, false);
        $persister = $this->getContainer()->get(Persister::class);

        yield $output->disconnect();
        yield $persister->save($project);
    }
}
