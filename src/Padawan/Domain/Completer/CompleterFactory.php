<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Completion\Context;
use Padawan\Domain\Scope;
use Padawan\Domain\Project;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Padawan\Domain\Event\CustomCompleterEvent;

class CompleterFactory
{
    const CUSTOM_COMPLETER = 'completer.custom';

    public function __construct(
        ClassNameCompleter $classNameCompleter,
        InterfaceNameCompleter $interfaceNameCompleter,
        NamespaceCompleter $namespaceCompleter,
        ObjectCompleter $objectCompleter,
        StaticCompleter $staticCompleter,
        UseCompleter $useCompleter,
        VarCompleter $varCompleter,
        GlobalFunctionsCompleter $functionsCompleter,
        GlobalCompleter $globalCompleter,
        EventDispatcher $dispatcher
    ) {
        $this->completers = [
            $classNameCompleter,
            $interfaceNameCompleter,
            $namespaceCompleter,
            $objectCompleter,
            $staticCompleter,
            $useCompleter,
            $varCompleter,
            $functionsCompleter,
            $globalCompleter,
        ];
        $this->dispatcher = $dispatcher;
    }

    public function getCompleters(Project $project, Context $context)
    {
        $completers = [];
        foreach($this->completers as $completer) {
            if ($completer->canHandle($project, $context)) {
                $completers[] = $completer;
            }
        }
        $event = new CustomCompleterEvent($project, $context);
        $this->dispatcher->dispatch(self::CUSTOM_COMPLETER, $event);
        if ($event->completer instanceof CompleterInterface) {
            $completers[] = $event->completer;
        }
        return $completers;
    }

    private $completers;
    private $dispatcher;
}
