<?php

/*
 * This file was developed after the fork from Symfony framework.
 *
 * (c) Matthew Gamble <git@matthewgamble.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MattyG\StateMachine;

use MattyG\StateMachine\Exception\LogicException;

final class TransitionGuardManager
{
    /**
     * @var callable[]
     */
    private $canGuards;

    /**
     * @var callable[]
     */
    private $leaveGuards;

    /**
     * @var callable[]
     */
    private $enterGuards;

    /**
     * @param callable[] $canGuards
     * @param callable[] $leaveGuards
     * @param callable[] $enterGuards
     */
    public function __construct(array $canGuards, array $leaveGuards, array $enterGuards)
    {
        $this->canGuards = $canGuards;
        $this->leaveGuards = $leaveGuards;
        $this->enterGuards = $enterGuards;
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param WorkflowInterface $workflow
     * @return bool
     * @throws LogicException
     */
    public function runCanGuards(object $subject, Transition $transition, WorkflowInterface $workflow): bool
    {
        foreach ($this->canGuards as $guard) {
            $can = $guard($subject, $transition, $workflow);
            if ($can === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param WorkflowInterface $workflow
     * @throws LogicException
     */
    public function runLeaveGuards(object $subject, Transition $transition, WorkflowInterface $workflow): void
    {
        foreach ($this->leaveGuards as $guard) {
            $guard($subject, $transition, $workflow);
        }
    }

    /**
     * @param object $subject
     * @param Transition $transition
     * @param WorkflowInterface $workflow
     * @throws LogicException
     */
    public function runEnterGuards(object $subject, Transition $transition, WorkflowInterface $workflow): void
    {
        foreach ($this->enterGuards as $guard) {
            $guard($subject, $transition, $workflow);
        }
    }
}
