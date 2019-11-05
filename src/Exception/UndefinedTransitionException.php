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

use MattyG\StateMachine\WorkflowInterface;

/**
 * Thrown by Workflow when an undefined transition is applied on a subject.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class UndefinedTransitionException extends TransitionException
{
    public function __construct(object $subject, string $transitionName, WorkflowInterface $workflow)
    {
        parent::__construct($subject, $transitionName, $workflow, sprintf('Transition "%s" is not defined for workflow "%s".', $transitionName, $workflow->getName()));
    }
}
