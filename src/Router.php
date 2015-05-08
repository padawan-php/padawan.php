<?php

class Router{

    /**
     * Finds command by its name
     *
     * @param $commandName String
     * @param $arguments array
     * @return \Command\CommandInterface
     */
    public function getCommand($commandName, array $arguments = []){
        if ($commandName == 'generate') {
            $command = new \Command\GenerateCommand;
        } else if($commandName == 'update') {
            $command = new \Command\UpdateCommand;
        } else if($commandName == 'complete'){
            $command = new \Command\CompleteCommand;
        } else if ($commandName == 'save'){
            $command = new \Command\SaveCommand;
        } else {
            $command = new \Command\ErrorCommand;
        }
        return $command;
    }
}
