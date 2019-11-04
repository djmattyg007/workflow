<?php

namespace MattyG\StateMachine\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use MattyG\StateMachine\EventListener\AuditTrailListener;
use MattyG\StateMachine\Tests\Subject;
use MattyG\StateMachine\Tests\WorkflowBuilderTrait;
use MattyG\StateMachine\Workflow;

class AuditTrailListenerTest extends TestCase
{
    use WorkflowBuilderTrait;

    public function testItWorks()
    {
        $definition = $this->createSimpleWorkflowDefinition();

        $object = new Subject('a');

        $logger = new Logger();

        $ed = new EventDispatcher();
        $ed->addSubscriber(new AuditTrailListener($logger));

        $workflow = new Workflow($definition, null, $ed);

        $workflow->apply($object, 't1');

        $expected = [
            'Leaving "a" for subject of class "MattyG\StateMachine\Tests\Subject" in workflow "unnamed".',
            'Transition "t1" for subject of class "MattyG\StateMachine\Tests\Subject" in workflow "unnamed".',
            'Entering "b" for subject of class "MattyG\StateMachine\Tests\Subject" in workflow "unnamed".',
        ];

        $this->assertSame($expected, $logger->logs);
    }
}

class Logger extends AbstractLogger
{
    public $logs = [];

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = $message;
    }
}
