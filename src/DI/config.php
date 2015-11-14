<?php

use Monolog\Logger;
use Domain\Generator\IndexGenerator;
use Framework\Generator\IndexGenerator as IndexGeneratorImpl;

return [
    Psr\Log\LoggerInterface::class => DI\factory(function() {
        $logger = new Logger('completer');

        $logger->pushHandler(new \Monolog\Handler\StreamHandler(
            "php://stdout"
        ));
        return $logger;
    }),
    IndexGenerator::class => DI\object(IndexGeneratorImpl::class)
];
