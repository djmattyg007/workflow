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

namespace MattyG\StateMachine\SupportStrategy;

use MattyG\StateMachine\WorkflowInterface;

/**
 * @author Amrouche Hamza <hamza.simperfit@gmail.com>
 */
interface WorkflowSupportStrategyInterface
{
    public function supports(WorkflowInterface $workflow, object $subject): bool;
}
