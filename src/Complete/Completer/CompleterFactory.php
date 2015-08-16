<?php

namespace Complete\Completer;

use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Project;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Complete\CustomCompleterEvent;

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
        EventDispatcher $dispatcher
    ) {
        $this->classNameCompleter = $classNameCompleter;
        $this->interfaceNameCompleter = $interfaceNameCompleter;
        $this->namespaceCompleter = $namespaceCompleter;
        $this->objectCompleter = $objectCompleter;
        $this->staticCompleter = $staticCompleter;
        $this->useCompleter = $useCompleter;
        $this->varCompleter = $varCompleter;
        $this->dispatcher = $dispatcher;
    }
    public function getCompleter(Context $context, Project $project)
    {
        if ($context->isNamespace()) {
            return $this->namespaceCompleter;
        } elseif ($context->isUse()) {
            return $this->useCompleter;
        } elseif ($context->isClassName()) {
            return $this->classNameCompleter;
        } elseif ($context->isInterfaceName()) {
            return $this->interfaceNameCompleter;
        } elseif ($context->isThis() || $context->isObject()) {
            return $this->objectCompleter;
        } elseif ($context->isClassStatic()) {
            return $this->staticCompleter;
        } elseif ($context->isVar()) {
            return $this->varCompleter;
        }
        $event = new CustomCompleterEvent($project, $context);
        $this->dispatcher->dispatch(self::CUSTOM_COMPLETER, $event);
        $completer = $event->completer;
        if ($completer) {
            return $completer;
        }
        return null;
    }

    private $classNameCompleter;
    private $interfaceNameCompleter;
    private $namespaceCompleter;
    private $objectCompleter;
    private $staticCompleter;
    private $useCompleter;
    private $varCompleter;
    private $dispatcher;
}
