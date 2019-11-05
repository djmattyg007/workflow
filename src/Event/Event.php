<?php

/*
 * This file was forked from the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Matthew Gamble <git@matthewgamble.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MattyG\StateMachine\Event;

use MattyG\StateMachine\TransitionInterface;
use MattyG\StateMachine\WorkflowInterface;
use Symfony\Contracts\EventDispatcher\Event as BaseEvent;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class Event extends BaseEvent
{
    /**
     * @var object
     */
    private $subject;

    /**
     * @var TransitionInterface
     */
    private $transition;

    /**
     * @var WorkflowInterface
     */
    private $workflow;

    /**
     * @param object $subject
     * @param TransitionInterface $transition
     * @param WorkflowInterface $workflow
     */
    public function __construct(object $subject, TransitionInterface $transition, WorkflowInterface $workflow)
    {
        $this->subject = $subject;
        $this->transition = $transition;
        $this->workflow = $workflow;
    }

    /**
     * @return object
     */
    public function getSubject(): object
    {
        return $this->subject;
    }

    /**
     * @return TransitionInterface
     */
    public function getTransition(): TransitionInterface
    {
        return $this->transition;
    }

    /**
     * @return string
     */
    public function getPreviousState(): string
    {
        return $this->transition->getFrom();
    }

    /**
     * @return string
     */
    public function getNewState(): string
    {
        return $this->transition->getTo();
    }

    /**
     * @return WorkflowInterface
     */
    public function getWorkflow(): WorkflowInterface
    {
        return $this->workflow;
    }

    /**
     * @return string
     */
    public function getWorkflowName(): string
    {
        return $this->workflow->getName();
    }

    public function getMetadata(string $key, object $subject)
    {
        return $this->workflow->getMetadataStore()->getMetadata($key, $subject);
    }
}
