<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Workflow\Validator;

use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\Exception\InvalidDefinitionException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class StateMachineValidator implements DefinitionValidatorInterface
{
    public function validate(Definition $definition, string $name): void
    {
        $transitionFromNames = [];
        foreach ($definition->getTransitions() as $transition) {
            // Enforcing uniqueness of the names of transitions starting at each node
            $from = $transition->getFrom();
            if (isset($transitionFromNames[$from][$transition->getName()])) {
                throw new InvalidDefinitionException(sprintf('A transition from a place/state must have an unique name. Multiple transitions named "%s" from place/state "%s" were found on StateMachine "%s".', $transition->getName(), $from, $name));
            }

            $transitionFromNames[$from][$transition->getName()] = true;
        }
    }
}
