<?php

namespace Command;

class ErrorCommand implements CommandInterface{
    public function run(array $arguments = []){
        echo "Error\n";
    }
}
