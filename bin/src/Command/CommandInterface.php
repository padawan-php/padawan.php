<?php

namespace Command;

interface CommandInterface {
    public function run(array $arguments = []);
}
