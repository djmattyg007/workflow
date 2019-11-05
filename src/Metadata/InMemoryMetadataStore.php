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

namespace MattyG\StateMachine\Metadata;

use MattyG\StateMachine\Transition;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
final class InMemoryMetadataStore implements MetadataStoreInterface
{
    use GetMetadataTrait;

    private $workflowMetadata;
    private $placesMetadata;
    private $transitionsMetadata;

    public function __construct(array $workflowMetadata = [], array $placesMetadata = [], \SplObjectStorage $transitionsMetadata = null)
    {
        $this->workflowMetadata = $workflowMetadata;
        $this->placesMetadata = $placesMetadata;
        $this->transitionsMetadata = $transitionsMetadata ?: new \SplObjectStorage();
    }

    public function getWorkflowMetadata(): array
    {
        return $this->workflowMetadata;
    }

    public function getPlaceMetadata(string $place): array
    {
        return $this->placesMetadata[$place] ?? [];
    }

    public function getTransitionMetadata(Transition $transition): array
    {
        return $this->transitionsMetadata[$transition] ?? [];
    }
}
