<?php

namespace MattyG\StateMachine\Tests\Validator;

use PHPUnit\Framework\TestCase;
use MattyG\StateMachine\Definition;
use MattyG\StateMachine\Exception\InvalidDefinitionException;
use MattyG\StateMachine\Transition;
use MattyG\StateMachine\Validator\StateMachineValidator;

class StateMachineValidatorTest extends TestCase
{
    public function testWithMultipleTransitionWithSameNameShareInput()
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('A transition from a place/state must have an unique name.');
        $places = ['a', 'b', 'c'];
        $transitions[] = new Transition('t1', 'a', 'b');
        $transitions[] = new Transition('t1', 'a', 'c');
        $definition = new Definition($places, $transitions);

        (new StateMachineValidator())->validate($definition, 'foo');

        // The graph looks like:
        //
        //   +----+     +----+     +---+
        //   | a  | --> | t1 | --> | b |
        //   +----+     +----+     +---+
        //    |
        //    |
        //    v
        //  +----+     +----+
        //  | t1 | --> | c  |
        //  +----+     +----+
    }

    public function testValid()
    {
        $places = ['a', 'b', 'c'];
        $transitions[] = new Transition('t1', 'a', 'b');
        $transitions[] = new Transition('t2', 'a', 'c');
        $definition = new Definition($places, $transitions);

        (new StateMachineValidator())->validate($definition, 'foo');

        // the test ensures that the validation does not fail (i.e. it does not throw any exceptions)
        $this->addToAssertionCount(1);

        // The graph looks like:
        //
        // +----+     +----+     +---+
        // | a  | --> | t1 | --> | b |
        // +----+     +----+     +---+
        //   |
        //   |
        //   v
        // +----+     +----+
        // | t2 | --> | c  |
        // +----+     +----+
    }
}
