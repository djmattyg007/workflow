<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MattyG\StateMachine\Event;

use MattyG\StateMachine\Marking;
use MattyG\StateMachine\Transition;
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
     * @var string
     */
    private $state;

    /**
     * @var Transition|null
     */
    private $transition;

    /**
     * @var WorkflowInterface|null
     */
    private $workflow;

    /**
     * @param object $subject
     * @param string $state
     * @param Transition|null $transition
     * @param WorkflowInterface|null $workflow
     */
    public function __construct(object $subject, string $state, Transition $transition = null, WorkflowInterface $workflow = null)
    {
        $this->subject = $subject;
        $this->state = $state;
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
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return Transition|null
     */
    public function getTransition(): ?Transition
    {
        return $this->transition;
    }

    /**
     * @return WorkflowInterface|null
     */
    public function getWorkflow(): ?WorkflowInterface
    {
        return $this->workflow;
    }

    /**
     * @return string
     */
    public function getWorkflowName(): string
    {
        if ($this->workflow !== null) {
            return $this->workflow->getName();
        } else {
            return '';
        }
    }

    public function getMetadata(string $key, object $subject)
    {
        return $this->workflow->getMetadataStore()->getMetadata($key, $subject);
    }
}
