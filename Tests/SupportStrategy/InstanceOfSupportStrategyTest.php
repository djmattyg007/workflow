<?php

namespace MattyG\StateMachine\Tests\SupportStrategy;

use PHPUnit\Framework\TestCase;
use MattyG\StateMachine\SupportStrategy\InstanceOfSupportStrategy;
use MattyG\StateMachine\Workflow;

class InstanceOfSupportStrategyTest extends TestCase
{
    public function testSupportsIfClassInstance()
    {
        $strategy = new InstanceOfSupportStrategy(Subject1::class);

        $this->assertTrue($strategy->supports($this->createWorkflow(), new Subject1()));
    }

    public function testSupportsIfNotClassInstance()
    {
        $strategy = new InstanceOfSupportStrategy(Subject2::class);

        $this->assertFalse($strategy->supports($this->createWorkflow(), new Subject1()));
    }

    private function createWorkflow()
    {
        return $this->getMockBuilder(Workflow::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

class Subject1
{
}
class Subject2
{
}
