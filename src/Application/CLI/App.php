<?php

namespace Application\CLI;

use Application\BaseApplication;

class App extends BaseApplication
{
    public function __construct($noFsIO)
    {
        parent::__construct($noFsIO);
        $this->router = new Router;
    }
    public function handle($request, $response, $data)
    {
        $result = parent::handle($request, $response, $data);
        return $result;
    }
    protected function getArguments($request, $response, $data)
    {
        return array_slice($request, 1);
    }
    protected function getCommandName($request)
    {
        return array_shift($request);
    }
}
