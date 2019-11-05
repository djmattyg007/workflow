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
 * @author Andrew Tch <andrew.tchircoff@gmail.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class TransitionException extends LogicException
{
    /**
     * @var object
     */
    private $subject;

    /**
     * @var string
     */
    private $transitionName;

    /**
     * @var WorkflowInterface
     */
    private $workflow;

    /**
     * @param object $subject
     * @param string $transitionName
     * @param WorkflowInterface $workflow
     * @param string $message
     */
    public function __construct(object $subject, string $transitionName, WorkflowInterface $workflow, string $message)
    {
        parent::__construct($message);

        $this->subject = $subject;
        $this->transitionName = $transitionName;
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
    public function getTransitionName(): string
    {
        return $this->transitionName;
    }

    /**
     * @return WorkflowInterface
     */
    public function getWorkflow(): WorkflowInterface
    {
        return $this->workflow;
    }
}
