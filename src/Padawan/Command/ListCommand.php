<?php

namespace Padawan\Command;

use Padawan\Domain\Project;
use Padawan\Domain\ProjectRepository;
use Padawan\Domain\Project\Node\ClassData;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Padawan\Framework\Application\Socket\SocketOutput;
use Padawan\Framework\Utils\PathResolver;

/**
 * Class ListCommand
 */
class ListCommand extends AsyncCommand
{
    protected function configure()
    {
        $this->setName("list")
            ->setDescription("Shows all classes with filepath")
            ->addArgument(
                "path",
                InputArgument::REQUIRED,
                "Path to the project root"
            );
    }
    protected function executeAsync(InputInterface $input, SocketOutput $output)
    {
        $path = $input->getArgument("path");

        $projectRepository = $this->getContainer()->get(ProjectRepository::class);
        /** @var PathResolver */
        $pathResolver = $this->getContainer()->get(PathResolver::class);
        /** @var Project */
        $project = $projectRepository->findByPath($path);
        $classesList = [];
        foreach ($project->getIndex()->getClasses() as $class) {
            $classesList[] = [
                'fqcn' => $class->fqcn->toString(),
                'filepath' => $pathResolver->join([$path, $class->file])
            ];
        }
        yield $output->write(json_encode($classesList));
    }
}
