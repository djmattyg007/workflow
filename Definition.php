<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Workflow;

use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class Definition
{
    /**
     * @var string[]
     */
    private $places = [];

    /**
     * @var Transition[]
     */
    private $transitions = [];

    /**
     * @var string|null
     */
    private $initialPlace = null;

    /**
     * @var MetadataStoreInterface
     */
    private $metadataStore;

    /**
     * @param string[] $places
     * @param Transition[] $transitions
     * @param string|null $initialPlace
     * @param MetadataStoreInterface|null $metadataStore
     */
    public function __construct(array $places, array $transitions, ?string $initialPlace = null, MetadataStoreInterface $metadataStore = null)
    {
        foreach ($places as $place) {
            $this->addPlace($place);
        }

        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }

        $this->setInitialPlace($initialPlace);

        $this->metadataStore = $metadataStore ?: new InMemoryMetadataStore();
    }

    /**
     * @return string
     */
    public function getInitialPlace(): string
    {
        return $this->initialPlace;
    }

    /**
     * @return string[]
     */
    public function getPlaces(): array
    {
        return $this->places;
    }

    /**
     * @return Transition[]
     */
    public function getTransitions(): array
    {
        return $this->transitions;
    }

    /**
     * @return MetadataStoreInterface
     */
    public function getMetadataStore(): MetadataStoreInterface
    {
        return $this->metadataStore;
    }

    /**
     * @param string|null $place
     */
    private function setInitialPlace(?string $place = null): void
    {
        if (!$place) {
            return;
        }

        if (!isset($this->places[$place])) {
            throw new LogicException(sprintf('Place "%s" cannot be the initial place as it does not exist.', $place));
        }

        $this->initialPlace = $place;
    }

    /**
     * @param string $place
     */
    private function addPlace(string $place): void
    {
        if ($this->initialPlace === null) {
            $this->initialPlace = $place;
        }

        $this->places[$place] = $place;
    }

    /**
     * @param Transition $transition
     */
    private function addTransition(Transition $transition): void
    {
        $name = $transition->getName();
        $from = $transition->getFrom();
        $to = $transition->getTo();

        if (!isset($this->places[$from])) {
            throw new LogicException(sprintf('Place "%s" referenced in transition "%s" does not exist.', $from, $name));
        }

        if (!isset($this->places[$to])) {
            throw new LogicException(sprintf('Place "%s" referenced in transition "%s" does not exist.', $to, $name));
        }

        $this->transitions[] = $transition;
    }
}
