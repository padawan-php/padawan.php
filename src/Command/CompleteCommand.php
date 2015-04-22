<?php

namespace Command;

class CompleteCommand extends AbstractCommand {

    public function run(array $arguments = []){
        $project = $arguments["project"];
        $contentManager = $this->get("Complete\ContentManager");
        $column = $arguments['column'];
        $file = $arguments['filepath'];
        $line = $arguments['line'];
        $content = $arguments['contents'];
        $completion = $contentManager->createCompletion(
            $project,
            $content,
            $line,
            $column,
            $file
        );
        return [
            "completion" => $this->prepareEntries(
                $completion["entries"]
            ),
            "context" => $completion["context"]
        ];
    }
    protected function prepareEntries(array $entries){
        $result = [];
        foreach($entries as $entry){
            $result[] = [
                "name" => $entry->getName(),
                "signature" => $entry->getSignature(),
                "description" => $entry->getDesc(),
                "menu" => $entry->getMenu()
            ];
        }
        return $result;
    }
}
