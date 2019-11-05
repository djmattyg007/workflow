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

namespace MattyG\StateMachine\SupportStrategy;

use MattyG\StateMachine\WorkflowInterface;

/**
 * @author Andreas Kleemann <akleemann@inviqa.com>
 * @author Amrouche Hamza <hamza.simperfit@gmail.com>
 */
final class InstanceOfSupportStrategy implements WorkflowSupportStrategyInterface
{
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(WorkflowInterface $workflow, object $subject): bool
    {
        return $subject instanceof $this->className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
