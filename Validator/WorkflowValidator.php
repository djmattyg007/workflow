<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MattyG\StateMachine\Validator;

use MattyG\StateMachine\Definition;
use MattyG\StateMachine\Exception\InvalidDefinitionException;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class WorkflowValidator implements DefinitionValidatorInterface
{
    public function validate(Definition $definition, string $name): void
    {
        // Make sure all transitions for one place has unique name.
        $places = array_fill_keys($definition->getPlaces(), []);
        foreach ($definition->getTransitions() as $transition) {
            $from = $transition->getFrom();
            if (\in_array($transition->getName(), $places[$from])) {
                throw new InvalidDefinitionException(sprintf('All transitions for a place must have a unique name. Multiple transitions named "%s" were found for place "%s" in workflow "%s".', $transition->getName(), $from, $name));
            }
            $places[$from][] = $transition->getName();
        }
    }
}
