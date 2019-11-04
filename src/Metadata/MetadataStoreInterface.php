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

use MattyG\StateMachine\Transition;

/**
 * MetadataStoreInterface is able to fetch metadata for a specific workflow.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
interface MetadataStoreInterface
{
    public function getWorkflowMetadata(): array;

    public function getPlaceMetadata(string $place): array;

    public function getTransitionMetadata(Transition $transition): array;

    /**
     * Returns the metadata for a specific subject.
     *
     * This is a proxy method.
     *
     * @param string|Transition|null $subject Use null to get workflow metadata
     *                                        Use a string (the place name) to get place metadata
     *                                        Use a Transition instance to get transition metadata
     */
    public function getMetadata(string $key, $subject = null);
}
