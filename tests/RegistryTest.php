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

namespace MattyG\StateMachine\Tests;

use PHPUnit\Framework\TestCase;
use MattyG\StateMachine\Definition;
use MattyG\StateMachine\Exception\InvalidArgumentException;
use MattyG\StateMachine\Registry;
use MattyG\StateMachine\StateAccessor\StateAccessorInterface;
use MattyG\StateMachine\SupportStrategy\WorkflowSupportStrategyInterface;
use MattyG\StateMachine\Workflow;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RegistryTest extends TestCase
{
    private $registry;

    protected function setUp(): void
    {
        $this->registry = new Registry();

        $this->registry->addWorkflow(
            new Workflow(
                new Definition([], []),
                $this->getMockBuilder(StateAccessorInterface::class)->getMock(),
                $this->getMockBuilder(EventDispatcherInterface::class)->getMock(),
                'workflow1'
            ),
            $this->createWorkflowSupportStrategy(Subject1::class)
        );
        $this->registry->addWorkflow(
            new Workflow(
                new Definition([], []),
                $this->getMockBuilder(StateAccessorInterface::class)->getMock(),
                $this->getMockBuilder(EventDispatcherInterface::class)->getMock(),
                'workflow2'
            ),
            $this->createWorkflowSupportStrategy(Subject2::class)
        );
        $this->registry->addWorkflow(
            new Workflow(
                new Definition([], []),
                $this->getMockBuilder(StateAccessorInterface::class)->getMock(),
                $this->getMockBuilder(EventDispatcherInterface::class)->getMock(),
                'workflow3'
            ),
            $this->createWorkflowSupportStrategy(Subject2::class)
        );
    }

    protected function tearDown(): void
    {
        $this->registry = null;
    }

    public function testGetWithSuccess()
    {
        $workflow = $this->registry->get(new Subject1());
        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertSame('workflow1', $workflow->getName());

        $workflow = $this->registry->get(new Subject1(), 'workflow1');
        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertSame('workflow1', $workflow->getName());

        $workflow = $this->registry->get(new Subject2(), 'workflow2');
        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertSame('workflow2', $workflow->getName());
    }

    public function testGetWithMultipleMatch()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many workflows (workflow2, workflow3) match this subject (MattyG\StateMachine\Tests\Subject2); set a different name on each and use the second (name) argument of this method.');
        $w1 = $this->registry->get(new Subject2());
        $this->assertInstanceOf(Workflow::class, $w1);
        $this->assertSame('workflow1', $w1->getName());
    }

    public function testGetWithNoMatch()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find a workflow for class "stdClass".');
        $w1 = $this->registry->get(new \stdClass());
        $this->assertInstanceOf(Workflow::class, $w1);
        $this->assertSame('workflow1', $w1->getName());
    }

    public function testAllWithOneMatchWithSuccess()
    {
        $workflows = $this->registry->all(new Subject1());
        $this->assertIsArray($workflows);
        $this->assertCount(1, $workflows);
        $this->assertInstanceOf(Workflow::class, $workflows[0]);
        $this->assertSame('workflow1', $workflows[0]->getName());
    }

    public function testAllWithMultipleMatchWithSuccess()
    {
        $workflows = $this->registry->all(new Subject2());
        $this->assertIsArray($workflows);
        $this->assertCount(2, $workflows);
        $this->assertInstanceOf(Workflow::class, $workflows[0]);
        $this->assertInstanceOf(Workflow::class, $workflows[1]);
        $this->assertSame('workflow2', $workflows[0]->getName());
        $this->assertSame('workflow3', $workflows[1]->getName());
    }

    public function testAllWithNoMatch()
    {
        $workflows = $this->registry->all(new \stdClass());
        $this->assertIsArray($workflows);
        $this->assertCount(0, $workflows);
    }

    private function createWorkflowSupportStrategy($supportedClassName)
    {
        $strategy = $this->getMockBuilder(WorkflowSupportStrategyInterface::class)->getMock();
        $strategy->expects($this->any())->method('supports')
            ->willReturnCallback(function ($workflow, $subject) use ($supportedClassName) {
                return $subject instanceof $supportedClassName;
            });

        return $strategy;
    }
}

class Subject1
{
}
class Subject2
{
}
