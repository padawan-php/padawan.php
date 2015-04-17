<?php

namespace Command;

class CompleteCommand extends AbstractCommand {
    public function run(array $arguments = []){
        $project = $arguments["project"];
        $contentManager = $this->get("Complete\ContentManager");
        $column = $file = $line = $content = "";
        if(array_key_exists("column", $arguments)){
            $column = $arguments["column"];
        }
        if(array_key_exists("column", $arguments)){
            $line = $arguments["line"];
        }
        if(array_key_exists("column", $arguments)){
            $content = $arguments["contents"];
        }
        if(array_key_exists("column", $arguments)){
            $file = $arguments["filepath"];
        }
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
