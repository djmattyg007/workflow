<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
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
