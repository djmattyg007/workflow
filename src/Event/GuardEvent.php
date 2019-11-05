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

use MattyG\StateMachine\Transition;
use MattyG\StateMachine\TransitionBlocker;
use MattyG\StateMachine\TransitionBlockerList;
use MattyG\StateMachine\WorkflowInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
final class GuardEvent extends Event
{
    /**
     * @var TransitionBlockerList
     */
    private $transitionBlockerList;

    /**
     * {@inheritdoc}
     */
    public function __construct(object $subject, Transition $transition, WorkflowInterface $workflow)
    {
        parent::__construct($subject, $transition, $workflow);

        $this->transitionBlockerList = new TransitionBlockerList();
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return !$this->transitionBlockerList->isEmpty();
    }

    public function setBlocked(bool $blocked): void
    {
        if (!$blocked) {
            $this->transitionBlockerList->clear();

            return;
        }

        $this->transitionBlockerList->add(TransitionBlocker::createUnknown());
    }

    public function getTransitionBlockerList(): TransitionBlockerList
    {
        return $this->transitionBlockerList;
    }

    public function addTransitionBlocker(TransitionBlocker $transitionBlocker): void
    {
        $this->transitionBlockerList->add($transitionBlocker);
    }
}
