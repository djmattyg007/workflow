<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MattyG\StateMachine\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MattyG\StateMachine\Event\Event;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class AuditTrailListener implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onLeave(Event $event)
    {
        $transition = $event->getTransition();
        $this->logger->info(sprintf('Leaving "%s" for subject of class "%s" in workflow "%s".', $transition->getFrom(), \get_class($event->getSubject()), $event->getWorkflowName()));
    }

    public function onTransition(Event $event)
    {
        $transition = $event->getTransition();
        $this->logger->info(sprintf('Transition "%s" for subject of class "%s" in workflow "%s".', $transition->getName(), \get_class($event->getSubject()), $event->getWorkflowName()));
    }

    public function onEnter(Event $event)
    {
        $transition = $event->getTransition();
        $this->logger->info(sprintf('Entering "%s" for subject of class "%s" in workflow "%s".', $transition->getTo(), \get_class($event->getSubject()), $event->getWorkflowName()));
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.leave' => ['onLeave'],
            'workflow.transition' => ['onTransition'],
            'workflow.enter' => ['onEnter'],
        ];
    }
}
