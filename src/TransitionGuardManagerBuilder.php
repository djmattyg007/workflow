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

final class TransitionGuardManagerBuilder
{
    /**
     * @var callable[]
     */
    private $canGuards = [];

    /**
     * @var callable[]
     */
    private $leaveGuards = [];

    /**
     * @var callable[]
     */
    private $enterGuards = [];

    /**
     * @param callable $guard
     */
    public function addCanGuard(callable $guard): void
    {
        $this->canGuards[] = $guard;
    }

    /**
     * @param callable $guard
     */
    public function addLeaveGuard(callable $guard): void
    {
        $this->leaveGuards[] = $guard;
    }

    /**
     * @param callable $guard
     */
    public function addEnterGuard(callable $guard): void
    {
        $this->enterGuards[] = $guard;
    }

    /**
     * @return TransitionGuardManager
     */
    public function build(): TransitionGuardManager
    {
        return new TransitionGuardManager(
            $this->canGuards,
            $this->leaveGuards,
            $this->enterGuards
        );
    }
}
