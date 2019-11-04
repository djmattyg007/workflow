<?php

namespace MattyG\StateMachine\Tests;

use PHPUnit\Framework\TestCase;
use MattyG\StateMachine\Transition;

class TransitionTest extends TestCase
{
    public function testConstructor()
    {
        $transition = new Transition('name', 'a', 'b');

        $this->assertSame('name', $transition->getName());
        $this->assertSame('a', $transition->getFrom());
        $this->assertSame('b', $transition->getTo());
    }
}
