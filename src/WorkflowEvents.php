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

namespace MattyG\StateMachine;

/**
 * To learn more about how workflow events work, check the documentation
 * entry at {@link https://symfony.com/doc/current/workflow/usage.html#using-events}.
 */
final class WorkflowEvents
{
    /**
     * @Event("MattyG\StateMachine\Event\GuardEvent")
     */
    const GUARD = 'workflow.guard';

    /**
     * @Event("MattyG\StateMachine\Event\AnnounceEvent")
     */
    const ANNOUNCE = 'workflow.announce';

    /**
     * @Event("MattyG\StateMachine\Event\CompletedEvent")
     */
    const COMPLETED = 'workflow.completed';

    /**
     * @Event("MattyG\StateMachine\Event\EnterEvent")
     */
    const ENTER = 'workflow.enter';

    /**
     * @Event("MattyG\StateMachine\Event\EnteredEvent")
     */
    const ENTERED = 'workflow.entered';

    /**
     * @Event("MattyG\StateMachine\Event\LeaveEvent")
     */
    const LEAVE = 'workflow.leave';

    /**
     * @Event("MattyG\StateMachine\Event\TransitionEvent")
     */
    const TRANSITION = 'workflow.transition';

    private function __construct()
    {
    }
}
