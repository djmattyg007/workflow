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

namespace MattyG\StateMachine\Exception;

use MattyG\StateMachine\TransitionBlockerList;
use MattyG\StateMachine\WorkflowInterface;

/**
 * Thrown by Workflow when a not enabled transition is applied on a subject.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class NotEnabledTransitionException extends TransitionException
{
    /**
     * @var TransitionBlockerList
     */
    private $transitionBlockerList;

    /**
     * @param object $subject
     * @param string $transitionName
     * @param WorkflowInterface $workflow
     * @param TransitionBlockerList $transitionBlockerList
     */
    public function __construct(object $subject, string $transitionName, WorkflowInterface $workflow, TransitionBlockerList $transitionBlockerList)
    {
        parent::__construct($subject, $transitionName, $workflow, sprintf('Transition "%s" is not enabled for workflow "%s".', $transitionName, $workflow->getName()));

        $this->transitionBlockerList = $transitionBlockerList;
    }

    /**
     * @return TransitionBlockerList
     */
    public function getTransitionBlockerList(): TransitionBlockerList
    {
        return $this->transitionBlockerList;
    }
}
