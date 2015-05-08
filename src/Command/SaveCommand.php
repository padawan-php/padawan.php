<?php

namespace Command;

class SaveCommand extends AbstractCommand {
    public function run(array $arguments = []){
        $project = $arguments["project"];
        /** @var \IO\Writer $writer */
        $writer = $this->get('IO\Writer');
        $writer->write($project);
        return [
            'status' => 'ok'
        ];
    }
}
