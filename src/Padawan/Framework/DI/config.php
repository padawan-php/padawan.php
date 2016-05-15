<?php

use Monolog\Logger;
use Padawan\Domain\Generator\IndexGenerator;
use Padawan\Framework\Generator\IndexGenerator as IndexGeneratorImpl;
use Padawan\Domain\ProjectRepository;
use Padawan\Framework\Domain\Project\Repository as ProjectRepositoryImpl;
use Padawan\Domain\Project\ClassRepository;
use Padawan\Framework\Domain\Project\ClassRepository as ClassRepositoryImpl;

return [
    Psr\Log\LoggerInterface::class => DI\factory(function() {
        $logger = new Logger('completer');

        $logger->pushHandler(new \Monolog\Handler\StreamHandler(
            "php://stdout"
        ));
        return $logger;
    }),
    IndexGenerator::class => DI\object(IndexGeneratorImpl::class),
    ProjectRepository::class => DI\object(ProjectRepositoryImpl::class),
    ClassRepository::class => DI\object(ClassRepositoryImpl::class)
];
