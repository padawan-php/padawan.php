<?php

namespace Fake;

class Request
{
    public function __construct($commandName, $query, $body)
    {
        $this->path = $commandName;
        $this->query = $query;
        $this->body = $body;
    }
    public function getQuery()
    {
        return $this->query;
    }
    public function getPath()
    {
        return $this->path;
    }

    public $query = "";
    public $path = "";
    public $body = "";
}
