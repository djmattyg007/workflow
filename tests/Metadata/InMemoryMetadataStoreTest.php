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

namespace MattyG\StateMachine\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use MattyG\StateMachine\Exception\InvalidArgumentException;
use MattyG\StateMachine\Metadata\InMemoryMetadataStore;
use MattyG\StateMachine\Transition;

/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class InMemoryMetadataStoreTest extends TestCase
{
    private $store;
    private $transition;

    protected function setUp(): void
    {
        $stateMachineMetadata = [
            'title' => 'statemachine title',
        ];
        $placesMetadata = [
            'place_a' => [
                'title' => 'place_a title',
            ],
        ];
        $transitionsMetadata = new \SplObjectStorage();
        $this->transition = new Transition('transition_1', 'from_1', 'to_1');
        $transitionsMetadata[$this->transition] = [
            'title' => 'transition_1 title',
        ];

        $this->store = new InMemoryMetadataStore($stateMachineMetadata, $placesMetadata, $transitionsMetadata);
    }

    public function testGetStateMachineMetadata()
    {
        $metadataBag = $this->store->getStateMachineMetadata();
        $this->assertSame('statemachine title', $metadataBag['title']);
    }

    public function testGetUnexistingPlaceMetadata()
    {
        $metadataBag = $this->store->getPlaceMetadata('place_b');
        $this->assertSame([], $metadataBag);
    }

    public function testGetExistingPlaceMetadata()
    {
        $metadataBag = $this->store->getPlaceMetadata('place_a');
        $this->assertSame('place_a title', $metadataBag['title']);
    }

    public function testGetUnexistingTransitionMetadata()
    {
        $metadataBag = $this->store->getTransitionMetadata(new Transition('transition_2', 'from_2', 'to_2'));
        $this->assertSame([], $metadataBag);
    }

    public function testGetExistingTransitionMetadata()
    {
        $metadataBag = $this->store->getTransitionMetadata($this->transition);
        $this->assertSame('transition_1 title', $metadataBag['title']);
    }

    public function testGetMetadata()
    {
        $this->assertSame('statemachine title', $this->store->getMetadata('title'));
        $this->assertNull($this->store->getMetadata('description'));
        $this->assertSame('place_a title', $this->store->getMetadata('title', 'place_a'));
        $this->assertNull($this->store->getMetadata('description', 'place_a'));
        $this->assertNull($this->store->getMetadata('description', 'place_b'));
        $this->assertSame('transition_1 title', $this->store->getMetadata('title', $this->transition));
        $this->assertNull($this->store->getMetadata('description', $this->transition));
        $this->assertNull($this->store->getMetadata('description', new Transition('transition_2', 'from_2', 'to_2')));
    }

    public function testGetMetadataWithUnknownType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find a MetadataBag for the subject of type "boolean".');
        $this->store->getMetadata('title', true);
    }
}
