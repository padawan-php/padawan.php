<?php

namespace Padawan\Framework\Application;

use Padawan\Command\AsyncCommand;
use Padawan\Command\KillCommand;
use Padawan\Command\ListCommand;
use Padawan\Framework\Application;
use Padawan\Command\CompleteCommand;
use Padawan\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Padawan\Framework\Application\Socket\HttpOutput;

/**
 * Class Socket
 */
class Socket extends Application
{
    public function __construct($loop)
    {
        parent::__construct("Padawan Server", $loop);
    }

    public function handle($request, HttpOutput $output)
    {
        if (!$request
            || !property_exists($request, "command")
            || !property_exists($request, "params")) {
            yield $output->write(json_encode([
                "error" => "Bad request"
            ]));
            return;
        }
        $arrayForInput = ['command' => $request->command];
        foreach($request->params as $key=>$value) {
            $arrayForInput[$key] =  $value;
        }
        $input = new ArrayInput($arrayForInput);
        $command = $this->find($request->command);
        try {
            if ($command instanceof AsyncCommand) {
                yield $command->run($input, $output);
            }
        } catch (\Exception $e) {
            printf("Error: %s\n", $e->getMessage());
            yield $output->write(json_encode([
                "error" => $e->getMessage()
            ]));
        }
    }

    protected function loadCommands()
    {
        $this->add(new CompleteCommand);
        $this->add(new UpdateCommand);
        $this->add(new ListCommand);
        $this->add(new KillCommand);
    }

}
