<?php

namespace Padawan\Command;

class SaveCommand extends AbstractCommand {
    public function run(array $arguments = []) {
        $project = $arguments["project"];
        /** @var \Padawan\Framework\IO\Writer $writer */
        $writer = $this->get('IO\Writer');
        $writer->write($project);
        return [
            'status' => 'ok'
        ];
    }
}
