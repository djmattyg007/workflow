<?php

/*
 * This file was developed after the fork from Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Matthew Gamble <git@matthewgamble.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MattyG\StateMachine\StateAccessor;

interface StateAccessorInterface
{
    /**
     * @param object $subject
     * @return string
     */
    public function getState(object $subject): string;

    /**
     * @param object $subject
     * @param string $state
     * @param array $context
     */
    public function setState(object $subject, string $state, array $context = []): void;
}
