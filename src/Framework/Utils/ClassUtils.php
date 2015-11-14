<?php

namespace Framework\Utils;

use Parser\Parser;

class ClassUtils {
    private $path;
    private $parser;

    public function __construct(PathResolver $path, Parser $parser) {
        $this->path = $path;
        $this->parser = $parser;
    }
    /** @return Parser */
    public function getParser() {
        return $this->parser;
    }
}
