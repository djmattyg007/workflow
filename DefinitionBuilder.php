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

use MattyG\StateMachine\Metadata\MetadataStoreInterface;

/**
 * Builds a definition.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DefinitionBuilder
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
     * @var MetadataStoreInterface|null
     */
    private $metadataStore = null;

    /**
     * @param string[]     $places
     * @param Transition[] $transitions
     */
    public function __construct(array $places = [], array $transitions = [])
    {
        $this->addPlaces($places);
        $this->addTransitions($transitions);
    }

    /**
     * @return Definition
     */
    public function build(): Definition
    {
        return new Definition($this->places, $this->transitions, $this->initialPlace, $this->metadataStore);
    }

    /**
     * @param string $initialPlaces
     */
    public function setInitialPlace(string $initialPlace): void
    {
        $this->initialPlace = $initialPlace;
    }

    /**
     * @param string $place
     */
    public function addPlace(string $place): void
    {
        if ($this->initialPlace === null) {
            $this->initialPlaces = $place;
        }

        $this->places[$place] = $place;
    }

    /**
     * @param string[] $places
     */
    public function addPlaces(array $places): void
    {
        foreach ($places as $place) {
            $this->addPlace($place);
        }
    }

    /**
     * @param Transition[] $transitions
     */
    public function addTransitions(array $transitions): void
    {
        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }
    }

    /**
     * @param Transition $transition
     */
    public function addTransition(Transition $transition): void
    {
        $this->transitions[] = $transition;
    }

    /**
     * @param MetadataStoreInterface $metadataStore
     */
    public function setMetadataStore(MetadataStoreInterface $metadataStore): void
    {
        $this->metadataStore = $metadataStore;
    }
}
