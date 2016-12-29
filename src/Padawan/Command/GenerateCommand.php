<?php

namespace Padawan\Command;

use Padawan\Domain\Project;
use Padawan\Domain\ProjectRepository;
use Padawan\Framework\Utils\PathResolver;
use Padawan\Domain\Generator\IndexGenerator;
use Padawan\Framework\Domain\Project\Persister;
use Padawan\Framework\Domain\Project\InMemoryIndex;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends CliCommand
{
    protected function configure()
    {
        $this->setName("generate")
            ->setDescription("Generates new index for the project")
            ->addArgument(
                "path",
                InputArgument::OPTIONAL,
                "Path to the project root. Default: current directory"
            );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument("path");
        /** @var PathResolver $pathResolver */
        $pathResolver = $this->getContainer()->get(PathResolver::class);
        if (empty($path)) {
            $path = $pathResolver->getWorkingDirectory();
        }

        $projectRepository = $this->getContainer()->get(ProjectRepository::class);
        $project = $projectRepository->findByPath($path);
        $generator = $this->getContainer()->get(IndexGenerator::class);

        $generator->generateProjectIndex($project);
        $persister = $this->getContainer()->get(Persister::class);

        $persister->saveNow($project);
    }
}
