<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Workflow;

use Symfony\Component\Workflow\Event\AnnounceEvent;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\EnterEvent;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\LeaveEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Exception\UndefinedTransitionException;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;
use Symfony\Component\Workflow\StateAccessor\MethodStateAccessor;
use Symfony\Component\Workflow\StateAccessor\StateAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Workflow implements WorkflowInterface
{
    /**
     * @var Definition
     */
    private $definition;

    /**
     * @var StateAccessorInterface
     */
    private $stateAccessor;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $name;

    /**
     * @param Definition $definition
     * @param StateAccessorInterface|null $stateAccessor
     * @param EventDispatcherInterface|null $dispatcher
     * @param string $name
     */
    public function __construct(Definition $definition, StateAccessorInterface $stateAccessor = null, EventDispatcherInterface $dispatcher = null, string $name = 'unnamed')
    {
        $this->definition = $definition;
        $this->stateAccessor = $stateAccessor ?: MethodStateAccessor::fromProperty("state");
        $this->dispatcher = $dispatcher;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getState(object $subject): string
    {
        $state = $this->stateAccessor->getState($subject);

        // check that the subject has a known place
        $places = $this->definition->getPlaces();
        if (!isset($places[$state])) {
            throw new LogicException(sprintf('State "%s" is not valid for workflow "%s".', $state, $this->name));
        }

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function can(object $subject, string $transitionName): bool
    {
        $transitions = $this->definition->getTransitions();
        $state = $this->getState($subject);

        foreach ($transitions as $transition) {
            if ($transition->getName() !== $transitionName) {
                continue;
            }

            $transitionBlockerList = $this->buildTransitionBlockerListForTransition($subject, $state, $transition);

            if ($transitionBlockerList->isEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTransitionBlockerList(object $subject, string $transitionName): TransitionBlockerList
    {
        $transitions = $this->definition->getTransitions();
        $state = $this->getState($subject);
        $transitionBlockerList = null;

        foreach ($transitions as $transition) {
            if ($transition->getName() !== $transitionName) {
                continue;
            }

            $transitionBlockerList = $this->buildTransitionBlockerListForTransition($subject, $state, $transition);

            if ($transitionBlockerList->isEmpty()) {
                return $transitionBlockerList;
            }

            // We prefer to return transitions blocker by something else than
            // state. Because it means the state was OK. Transitions are
            // deterministic: it's not possible to have many transitions enabled
            // at the same time that match the same state with the same name.
            if (!$transitionBlockerList->has(TransitionBlocker::BLOCKED_BY_STATE)) {
                return $transitionBlockerList;
            }
        }

        if (!$transitionBlockerList) {
            throw new UndefinedTransitionException($subject, $transitionName, $this);
        }

        return $transitionBlockerList;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(object $subject, string $transitionName, array $context = []): string
    {
        $originalState = $this->getState($subject);

        $transitionBlockerList = null;
        $approvedTransition = null;

        foreach ($this->definition->getTransitions() as $transition) {
            if ($transition->getName() !== $transitionName) {
                continue;
            }

            $transitionBlockerList = $this->buildTransitionBlockerListForTransition($subject, $originalState, $transition);
            if ($transitionBlockerList->isEmpty()) {
                $approvedTransition = $transition;
                break;
            }
        }

        if (!$transitionBlockerList) {
            throw new UndefinedTransitionException($subject, $transitionName, $this);
        }

        if (!$approvedTransition) {
            throw new NotEnabledTransitionException($subject, $transitionName, $this, $transitionBlockerList);
        }

        $newState = $approvedTransition->getTo();

        $this->leave($subject, $approvedTransition, $originalState);

        $context = $this->transition($subject, $transition, $newState, $context);

        $this->enter($subject, $approvedTransition, $newState);

        $this->stateAccessor->setState($subject, $newState, $context);

        $this->entered($subject, $transition, $newState);

        $this->completed($subject, $transition, $newState);

        $this->announce($subject, $transition, $newState);

        return $newState;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledTransitions(object $subject): array
    {
        $enabledTransitions = [];
        $state = $this->getState($subject);

        foreach ($this->definition->getTransitions() as $transition) {
            $transitionBlockerList = $this->buildTransitionBlockerListForTransition($subject, $state, $transition);
            if ($transitionBlockerList->isEmpty()) {
                $enabledTransitions[] = $transition;
            }
        }

        return $enabledTransitions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(): Definition
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getStateAccessor(): StateAccessorInterface
    {
        return $this->stateAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataStore(): MetadataStoreInterface
    {
        return $this->definition->getMetadataStore();
    }

    /**
     * @param object $subject
     * @param string $state
     * @param Transition $transition
     * @return TransitionBlockerList
     */
    private function buildTransitionBlockerListForTransition(object $subject, string $state, Transition $transition): TransitionBlockerList
    {
        $from = $transition->getFrom();
        if ($from !== $state) {
            return new TransitionBlockerList([
                TransitionBlocker::createBlockedByState($state),
            ]);
        }

        $event = $this->guardTransition($subject, $state, $transition);

        if ($event !== null && $event->isBlocked()) {
            return $event->getTransitionBlockerList();
        }

        return new TransitionBlockerList();
    }

    /**
     * @param object $subject
     * @param string $state
     * @param Transition $transition
     * @return GuardEvent|null
     */
    private function guardTransition(object $subject, string $state, Transition $transition): ?GuardEvent
    {
        if (null === $this->dispatcher) {
            return null;
        }

        $event = new GuardEvent($subject, $state, $transition, $this);

        $this->dispatcher->dispatch($event, WorkflowEvents::GUARD);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.guard', $this->name));
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.guard.%s', $this->name, $transition->getName()));

        return $event;
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param string $state
     */
    private function leave(object $subject, Transition $transition, string $state): void
    {
        if (null === $this->dispatcher) {
            return;
        }

        $event = new LeaveEvent($subject, $state, $transition, $this);

        $this->dispatcher->dispatch($event, WorkflowEvents::LEAVE);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.leave', $this->name));
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.leave.%s', $this->name, $transition->getFrom()));
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param string $state
     * @param array $context
     * @return array
     */
    private function transition(object $subject, Transition $transition, string $state, array $context): array
    {
        if (null === $this->dispatcher) {
            return $context;
        }

        $event = new TransitionEvent($subject, $state, $transition, $this);
        $event->setContext($context);

        $this->dispatcher->dispatch($event, WorkflowEvents::TRANSITION);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.transition', $this->name));
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.transition.%s', $this->name, $transition->getName()));

        return $event->getContext();
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param string $state
     */
    private function enter(object $subject, Transition $transition, string $state): void
    {
        if (null === $this->dispatcher) {
            return;
        }

        $event = new EnterEvent($subject, $state, $transition, $this);

        $this->dispatcher->dispatch($event, WorkflowEvents::ENTER);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.enter', $this->name));
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.enter.%s', $this->name, $transition->getTo()));
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param string $state
     */
    private function entered(object $subject, Transition $transition, string $state): void
    {
        if (null === $this->dispatcher) {
            return;
        }

        $event = new EnteredEvent($subject, $state, $transition, $this);

        $this->dispatcher->dispatch($event, WorkflowEvents::ENTERED);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.entered', $this->name));
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.entered.%s', $this->name, $transition->getTo()));
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param string $state
     */
    private function completed(object $subject, Transition $transition, string $state): void
    {
        if (null === $this->dispatcher) {
            return;
        }

        $event = new CompletedEvent($subject, $state, $transition, $this);

        $this->dispatcher->dispatch($event, WorkflowEvents::COMPLETED);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.completed', $this->name));
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.completed.%s', $this->name, $transition->getName()));
    }

    /**
     * @param object $subject
     * @param Transition $initialTransition
     * @param string $state
     */
    private function announce(object $subject, Transition $initialTransition, string $state): void
    {
        if (null === $this->dispatcher) {
            return;
        }

        $event = new AnnounceEvent($subject, $state, $initialTransition, $this);

        $this->dispatcher->dispatch($event, WorkflowEvents::ANNOUNCE);
        $this->dispatcher->dispatch($event, sprintf('workflow.%s.announce', $this->name));

        foreach ($this->getEnabledTransitions($subject) as $transition) {
            $this->dispatcher->dispatch($event, sprintf('workflow.%s.announce.%s', $this->name, $transition->getName()));
        }
    }
}
