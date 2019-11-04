<?php

namespace Symfony\Component\Workflow\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\StateAccessor\MethodStateAccessor;
use Symfony\Component\Workflow\StateAccessor\StateAccessorInterface;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Component\Workflow\Workflow;

class WorkflowTest extends TestCase
{
    use WorkflowBuilderTrait;

    public function testGetStateWithImpossiblePlace()
    {
        $this->expectException('Symfony\Component\Workflow\Exception\LogicException');
        $this->expectExceptionMessage('State "nope" is not valid for workflow "unnamed".');
        $subject = new Subject('nope');
        $workflow = new Workflow(new Definition([], []));

        $workflow->getState($subject);
    }

    public function testGetStateWithExistingState()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('b');
        $workflow = new Workflow($definition);

        $state = $workflow->getState($subject);

        $this->assertSame('b', $state);
    }

    public function testCanWithUnexistingTransition()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $this->assertFalse($workflow->can($subject, 'foobar'));
    }

    public function testCan()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $this->assertTrue($workflow->can($subject, 't1-1'));
        $this->assertTrue($workflow->can($subject, 't1-2'));
        $this->assertFalse($workflow->can($subject, 't2'));

        $subject->setState('b');
        $this->assertFalse($workflow->can($subject, 't1-1'));
        $this->assertFalse($workflow->can($subject, 't1-2'));
        $this->assertTrue($workflow->can($subject, 't2'));

        $subject->setState('c');
        $this->assertFalse($workflow->can($subject, 't1-1'));
        $this->assertFalse($workflow->can($subject, 't1-2'));
        $this->assertTrue($workflow->can($subject, 't2'));

        $subject->setState('f');
        $this->assertFalse($workflow->can($subject, 't5'));
        $this->assertTrue($workflow->can($subject, 't6'));
    }

    public function testCanWithGuard()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener('workflow.workflow_name.guard.t1-1', function (GuardEvent $event) {
            $event->setBlocked(true);
        });
        $workflow = new Workflow($definition, null, $eventDispatcher, 'workflow_name');

        $this->assertFalse($workflow->can($subject, 't1-1'));
    }

    public function testCanDoesNotTriggerGuardEventsForNotEnabledTransitions()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');

        $dispatchedEvents = [];
        $eventDispatcher = new EventDispatcher();

        $workflow = new Workflow($definition, null, $eventDispatcher, 'workflow_name');
        $workflow->apply($subject, 't1-1');
        $workflow->apply($subject, 't2');

        $eventDispatcher->addListener('workflow.workflow_name.guard.t3', function () use (&$dispatchedEvents) {
            $dispatchedEvents[] = 'workflow_name.guard.t3';
        });
        $eventDispatcher->addListener('workflow.workflow_name.guard.t4', function () use (&$dispatchedEvents) {
            $dispatchedEvents[] = 'workflow_name.guard.t4';
        });

        $workflow->can($subject, 't3');

        $this->assertSame(['workflow_name.guard.t3'], $dispatchedEvents);
    }

    public function testCanWithSameNameTransition()
    {
        $definition = $this->createWorkflowWithSameNameTransition();
        $workflow = new Workflow($definition);

        $subject = new Subject('a');
        $this->assertTrue($workflow->can($subject, 'a_to_b'));
        $this->assertTrue($workflow->can($subject, 'a_to_c'));
        $this->assertFalse($workflow->can($subject, 'b_to_c'));
        $this->assertFalse($workflow->can($subject, 'to_a'));

        $subject->setState('b');
        $this->assertFalse($workflow->can($subject, 'a_to_b'));
        $this->assertFalse($workflow->can($subject, 'a_to_c'));
        $this->assertTrue($workflow->can($subject, 'b_to_c'));
        $this->assertTrue($workflow->can($subject, 'to_a'));

        $subject->setState('c');
        $this->assertFalse($workflow->can($subject, 'a_to_b'));
        $this->assertFalse($workflow->can($subject, 'a_to_c'));
        $this->assertFalse($workflow->can($subject, 'b_to_c'));
        $this->assertTrue($workflow->can($subject, 'to_a'));
    }

    public function testBuildTransitionBlockerListReturnsUndefinedTransition()
    {
        $this->expectException('Symfony\Component\Workflow\Exception\UndefinedTransitionException');
        $this->expectExceptionMessage('Transition "404 Not Found" is not defined for workflow "unnamed".');
        $definition = $this->createSimpleWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $workflow->buildTransitionBlockerList($subject, '404 Not Found');
    }

    public function testBuildTransitionBlockerList()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $this->assertTrue($workflow->buildTransitionBlockerList($subject, 't1-1')->isEmpty());
        $this->assertTrue($workflow->buildTransitionBlockerList($subject, 't1-2')->isEmpty());
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't2')->isEmpty());
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't3')->isEmpty());

        $subject->setState('b');
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't1-1')->isEmpty());
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't1-2')->isEmpty());
        $this->assertTrue($workflow->buildTransitionBlockerList($subject, 't2')->isEmpty());
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't3')->isEmpty());

        $subject->setState('c');
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't1-1')->isEmpty());
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't1-2')->isEmpty());
        $this->assertTrue($workflow->buildTransitionBlockerList($subject, 't2')->isEmpty());
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't3')->isEmpty());

        $subject->setState('f');
        $this->assertFalse($workflow->buildTransitionBlockerList($subject, 't5')->isEmpty());
        $this->assertTrue($workflow->buildTransitionBlockerList($subject, 't6')->isEmpty());
    }

    public function testBuildTransitionBlockerListReturnsReasonsProvidedByState()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $transitionBlockerList = $workflow->buildTransitionBlockerList($subject, 't2');
        $this->assertCount(1, $transitionBlockerList);
        $blockers = iterator_to_array($transitionBlockerList);
        $this->assertSame('The state does not enable the transition.', $blockers[0]->getMessage());
        $this->assertSame('29beefc4-6b3e-4726-0d17-f38bd6d16e34', $blockers[0]->getCode());
    }

    public function testBuildTransitionBlockerListReturnsReasonsProvidedInGuards()
    {
        $definition = $this->createSimpleWorkflowDefinition();
        $subject = new Subject('a');
        $dispatcher = new EventDispatcher();
        $workflow = new Workflow($definition, null, $dispatcher);

        $dispatcher->addListener('workflow.guard', function (GuardEvent $event) {
            $event->addTransitionBlocker(new TransitionBlocker('Transition blocker 1', 'blocker_1'));
            $event->addTransitionBlocker(new TransitionBlocker('Transition blocker 2', 'blocker_2'));
        });
        $dispatcher->addListener('workflow.guard', function (GuardEvent $event) {
            $event->addTransitionBlocker(new TransitionBlocker('Transition blocker 3', 'blocker_3'));
        });
        $dispatcher->addListener('workflow.guard', function (GuardEvent $event) {
            $event->setBlocked(true);
        });

        $transitionBlockerList = $workflow->buildTransitionBlockerList($subject, 't1');
        $this->assertCount(4, $transitionBlockerList);
        $blockers = iterator_to_array($transitionBlockerList);
        $this->assertSame('Transition blocker 1', $blockers[0]->getMessage());
        $this->assertSame('blocker_1', $blockers[0]->getCode());
        $this->assertSame('Transition blocker 2', $blockers[1]->getMessage());
        $this->assertSame('blocker_2', $blockers[1]->getCode());
        $this->assertSame('Transition blocker 3', $blockers[2]->getMessage());
        $this->assertSame('blocker_3', $blockers[2]->getCode());
        $this->assertSame('Unknown reason.', $blockers[3]->getMessage());
        $this->assertSame('e8b5bbb9-5913-4b98-bfa6-65dbd228a82a', $blockers[3]->getCode());
    }

    public function testApplyWithNotExisingTransition()
    {
        $this->expectException('Symfony\Component\Workflow\Exception\UndefinedTransitionException');
        $this->expectExceptionMessage('Transition "404 Not Found" is not defined for workflow "unnamed".');
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $workflow->apply($subject, '404 Not Found');
    }

    public function testApplyWithNotEnabledTransition()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        try {
            $workflow->apply($subject, 't2');

            $this->fail('Should throw an exception');
        } catch (NotEnabledTransitionException $e) {
            $this->assertSame('Transition "t2" is not enabled for workflow "unnamed".', $e->getMessage());
            $this->assertCount(1, $e->getTransitionBlockerList());
            $list = iterator_to_array($e->getTransitionBlockerList());
            $this->assertSame('The state does not enable the transition.', $list[0]->getMessage());
            $this->assertSame($e->getWorkflow(), $workflow);
            $this->assertSame($e->getSubject(), $subject);
            $this->assertSame($e->getTransitionName(), 't2');
        }
    }

    public function testApply()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $newState = $workflow->apply($subject, 't1-1');

        $this->assertNotSame('a', $newState);
        $this->assertSame('b', $newState);
    }

    public function testApplyWithSameNameTransition()
    {
        $subject = new Subject('a');
        $definition = $this->createWorkflowWithSameNameTransition();
        $workflow = new Workflow($definition);

        $newState = $workflow->apply($subject, 'a_to_b');
        $this->assertSame('b', $newState);

        $newState = $workflow->apply($subject, 'to_a');
        $this->assertSame('a', $newState);

        $newState = $workflow->apply($subject, 'a_to_c');
        $this->assertSame('c', $newState);

        $newState = $workflow->apply($subject, 'to_a');
        $this->assertSame('a', $newState);
    }

    public function testApplyWithSameNameTransition2()
    {
        $subject = new Subject('a');

        $places = range('a', 'd');
        $transitions = [];
        $transitions[] = new Transition('t', 'a', 'c');
        $transitions[] = new Transition('t', 'b', 'd');
        $definition = new Definition($places, $transitions);
        $workflow = new Workflow($definition);

        $newState = $workflow->apply($subject, 't');
        $this->assertSame('c', $newState);
    }

    public function testApplyWithSameNameTransition3()
    {
        $subject = new Subject('a');

        $places = range('a', 'd');
        $transitions = [];
        $transitions[] = new Transition('t', 'a', 'b');
        $transitions[] = new Transition('t', 'b', 'c');
        $transitions[] = new Transition('t', 'c', 'd');
        $definition = new Definition($places, $transitions);
        $workflow = new Workflow($definition);

        $newState = $workflow->apply($subject, 't');
        $this->assertSame('b', $newState);
    }

    public function testApplyWithEventDispatcher()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $eventDispatcher = new EventDispatcherMock();
        $workflow = new Workflow($definition, null, $eventDispatcher, 'workflow_name');

        $eventNameExpected = [
            'workflow.guard',
            'workflow.workflow_name.guard',
            'workflow.workflow_name.guard.t1-1',
            'workflow.leave',
            'workflow.workflow_name.leave',
            'workflow.workflow_name.leave.a',
            'workflow.transition',
            'workflow.workflow_name.transition',
            'workflow.workflow_name.transition.t1-1',
            'workflow.enter',
            'workflow.workflow_name.enter',
            'workflow.workflow_name.enter.b',
            'workflow.entered',
            'workflow.workflow_name.entered',
            'workflow.workflow_name.entered.b',
            'workflow.completed',
            'workflow.workflow_name.completed',
            'workflow.workflow_name.completed.t1-1',
            // Following events are fired because of announce() method
            'workflow.announce',
            'workflow.workflow_name.announce',
            'workflow.guard',
            'workflow.workflow_name.guard',
            'workflow.workflow_name.guard.t2',
            'workflow.workflow_name.announce.t2',
        ];

        $workflow->apply($subject, 't1-1');

        $this->assertSame($eventNameExpected, $eventDispatcher->dispatchedEvents);
    }

    public function testApplyDoesNotTriggerExtraGuardWithEventDispatcher()
    {
        $transitions[] = new Transition('a-b', 'a', 'b');
        $transitions[] = new Transition('a-c', 'a', 'c');
        $definition = new Definition(['a', 'b', 'c'], $transitions);

        $subject = new Subject('a');
        $eventDispatcher = new EventDispatcherMock();
        $workflow = new Workflow($definition, null, $eventDispatcher, 'workflow_name');

        $eventNameExpected = [
            'workflow.guard',
            'workflow.workflow_name.guard',
            'workflow.workflow_name.guard.a-b',
            'workflow.leave',
            'workflow.workflow_name.leave',
            'workflow.workflow_name.leave.a',
            'workflow.transition',
            'workflow.workflow_name.transition',
            'workflow.workflow_name.transition.a-b',
            'workflow.enter',
            'workflow.workflow_name.enter',
            'workflow.workflow_name.enter.b',
            'workflow.entered',
            'workflow.workflow_name.entered',
            'workflow.workflow_name.entered.b',
            'workflow.completed',
            'workflow.workflow_name.completed',
            'workflow.workflow_name.completed.a-b',
            'workflow.announce',
            'workflow.workflow_name.announce',
        ];

        $workflow->apply($subject, 'a-b');

        $this->assertSame($eventNameExpected, $eventDispatcher->dispatchedEvents);
    }

    public function testApplyWithContext()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener('workflow.transition', function (TransitionEvent $event) {
            $event->setContext(array_merge($event->getContext(), ['user' => 'admin']));
        });
        $workflow = new Workflow($definition, null, $eventDispatcher);

        $workflow->apply($subject, 't1-1', ['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar', 'user' => 'admin'], $subject->getContext());
    }

    public function testEventName()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $dispatcher = new EventDispatcher();
        $name = 'workflow_name';
        $workflow = new Workflow($definition, null, $dispatcher, $name);

        $assertWorkflowName = function (Event $event) use ($name) {
            $this->assertEquals($name, $event->getWorkflowName());
        };

        $eventNames = [
            'workflow.guard',
            'workflow.leave',
            'workflow.transition',
            'workflow.enter',
            'workflow.entered',
            'workflow.announce',
        ];

        foreach ($eventNames as $eventName) {
            $dispatcher->addListener($eventName, $assertWorkflowName);
        }

        $workflow->apply($subject, 't1-1');
    }

    public function testGetEnabledTransitions()
    {
        $definition = $this->createComplexWorkflowDefinition();
        $subject = new Subject('a');
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener('workflow.workflow_name.guard.t1-1', function (GuardEvent $event) {
            $event->setBlocked(true);
        });
        $eventDispatcher->addListener('workflow.workflow_name.guard.t1-2', function (GuardEvent $event) {
            $event->setBlocked(true);
        });
        $workflow = new Workflow($definition, null, $eventDispatcher, 'workflow_name');

        $this->assertEmpty($workflow->getEnabledTransitions($subject));

        $subject->setState('d');
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount(2, $transitions);
        $this->assertSame('t3', $transitions[0]->getName());
        $this->assertSame('t4', $transitions[1]->getName());

        $subject->setState('e');
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount(1, $transitions);
        $this->assertSame('t5', $transitions[0]->getName());
    }

    public function testGetEnabledTransitionsWithSameNameTransition()
    {
        $definition = $this->createWorkflowWithSameNameTransition();
        $subject = new Subject('a');
        $workflow = new Workflow($definition);

        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount(2, $transitions);
        $this->assertSame('a_to_b', $transitions[0]->getName());
        $this->assertSame('a_to_c', $transitions[1]->getName());

        $subject->setState('b');
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount(2, $transitions);
        $this->assertSame('b_to_c', $transitions[0]->getName());
        $this->assertSame('to_a', $transitions[1]->getName());

        $subject->setState('c');
        $transitions = $workflow->getEnabledTransitions($subject);
        $this->assertCount(1, $transitions);
        $this->assertSame('to_a', $transitions[0]->getName());
    }
}

class EventDispatcherMock implements \Symfony\Component\EventDispatcher\EventDispatcherInterface
{
    public $dispatchedEvents = [];

    public function dispatch($event, string $eventName = null): object
    {
        $this->dispatchedEvents[] = $eventName;

        return $event;
    }

    public function addListener($eventName, $listener, $priority = 0)
    {
    }

    public function addSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
    }

    public function removeListener($eventName, $listener)
    {
    }

    public function removeSubscriber(\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
    }

    public function getListeners($eventName = null): array
    {
    }

    public function getListenerPriority($eventName, $listener): ?int
    {
    }

    public function hasListeners($eventName = null): bool
    {
    }
}
