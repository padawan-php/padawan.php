<?php

use Monolog\Logger;

return [
    Psr\Log\LoggerInterface::class => DI\factory(function () {
        $logger = new Logger('completer');

        $logger->pushHandler(new \Monolog\Handler\StreamHandler(
            "php://stdout"
        ));
        return $logger;
    }),
];
