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

namespace MattyG\StateMachine\Metadata;

use MattyG\StateMachine\Exception\InvalidArgumentException;
use MattyG\StateMachine\Transition;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
trait GetMetadataTrait
{
    public function getMetadata(string $key, $subject = null)
    {
        if (null === $subject) {
            return $this->getWorkflowMetadata()[$key] ?? null;
        }

        if (\is_string($subject)) {
            $metadataBag = $this->getPlaceMetadata($subject);
            if (!$metadataBag) {
                return null;
            }

            return $metadataBag[$key] ?? null;
        }

        if ($subject instanceof Transition) {
            $metadataBag = $this->getTransitionMetadata($subject);
            if (!$metadataBag) {
                return null;
            }

            return $metadataBag[$key] ?? null;
        }

        throw new InvalidArgumentException(sprintf('Could not find a MetadataBag for the subject of type "%s".', \is_object($subject) ? \get_class($subject) : \gettype($subject)));
    }
}
