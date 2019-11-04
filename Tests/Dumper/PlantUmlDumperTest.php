<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MattyG\StateMachine\Tests\Dumper;

use PHPUnit\Framework\TestCase;
use MattyG\StateMachine\Dumper\PlantUmlDumper;
use MattyG\StateMachine\Tests\WorkflowBuilderTrait;

class PlantUmlDumperTest extends TestCase
{
    use WorkflowBuilderTrait;

    /**
     * @dataProvider provideWorkflowDefinitionWithoutState
     */
    public function testDumpWorkflowWithoutState($definition, $state, $expectedFileName, $title)
    {
        $dumper = new PlantUmlDumper(PlantUmlDumper::WORKFLOW_TRANSITION);
        $dump = $dumper->dump($definition, $state, ['title' => $title]);
        // handle windows, and avoid to create more fixtures
        $dump = str_replace(PHP_EOL, "\n", $dump.PHP_EOL);
        $file = $this->getFixturePath($expectedFileName, PlantUmlDumper::WORKFLOW_TRANSITION);
        $this->assertStringEqualsFile($file, $dump);
    }

    public function provideWorkflowDefinitionWithoutState()
    {
        yield [$this->createSimpleWorkflowDefinition(), null, 'simple-workflow-nostate', 'SimpleDiagram'];
        yield [$this->createComplexWorkflowDefinition(), null, 'complex-workflow-nostate', 'ComplexDiagram'];
        yield [$this->createSimpleWorkflowDefinition(), 'b', 'simple-workflow-state', 'SimpleDiagram'];
        yield [$this->createComplexWorkflowDefinition(), 'c', 'complex-workflow-state', 'ComplexDiagram'];
    }

    /**
     * @dataProvider provideStateMachineDefinitionWithoutState
     */
    public function testDumpStateMachineWithoutState($definition, $state, $expectedFileName, $title)
    {
        $dumper = new PlantUmlDumper(PlantUmlDumper::STATEMACHINE_TRANSITION);
        $dump = $dumper->dump($definition, $state, ['title' => $title]);
        // handle windows, and avoid to create more fixtures
        $dump = str_replace(PHP_EOL, "\n", $dump.PHP_EOL);
        $file = $this->getFixturePath($expectedFileName, PlantUmlDumper::STATEMACHINE_TRANSITION);
        $this->assertStringEqualsFile($file, $dump);
    }

    public function provideStateMachineDefinitionWithoutState()
    {
        yield [$this->createComplexStateMachineDefinition(), null, 'complex-state-machine-nostate', 'SimpleDiagram'];
        yield [$this->createComplexStateMachineDefinition(), 'c', 'complex-state-machine-state', 'SimpleDiagram'];
    }

    private function getFixturePath($name, $transitionType)
    {
        return __DIR__.'/../fixtures/puml/'.$transitionType.'/'.$name.'.puml';
    }
}
