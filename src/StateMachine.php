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

use MattyG\StateMachine\MarkingStore\MarkingStoreInterface;
use MattyG\StateMachine\MarkingStore\MethodMarkingStore;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StateMachine extends Workflow
{
}
