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

namespace MattyG\StateMachine\Dumper;

use MattyG\StateMachine\Definition;

class StateMachineGraphvizDumper extends GraphvizDumper
{
    /**
     * {@inheritdoc}
     *
     * Dumps the workflow as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes (places)
     *  * edge: The default options for edges
     */
    public function dump(Definition $definition, ?string $state = null, array $options = [])
    {
        $places = $this->findPlaces($definition, $state);
        $edges = $this->findEdges($definition);

        $options = array_replace_recursive(self::$defaultOptions, $options);

        return $this->startDot($options)
            .$this->addPlaces($places)
            .$this->addEdges($edges)
            .$this->endDot()
        ;
    }

    /**
     * @internal
     */
    protected function findEdges(Definition $definition): array
    {
        $workflowMetadata = $definition->getMetadataStore();

        $edges = [];

        foreach ($definition->getTransitions() as $transition) {
            $attributes = [];

            $transitionName = $workflowMetadata->getMetadata('label', $transition) ?? $transition->getName();

            $labelColor = $workflowMetadata->getMetadata('color', $transition);
            if (null !== $labelColor) {
                $attributes['fontcolor'] = $labelColor;
            }
            $arrowColor = $workflowMetadata->getMetadata('arrow_color', $transition);
            if (null !== $arrowColor) {
                $attributes['color'] = $arrowColor;
            }

            $edge = [
                "name" => $transitionName,
                "to" => $transition->getTo(),
                "attributes" => $attributes,
            ];
            $edges[$transition->getFrom()][] = $edge;
        }

        return $edges;
    }

    /**
     * @internal
     */
    protected function addEdges(array $edges): string
    {
        $code = '';

        foreach ($edges as $id => $edges) {
            foreach ($edges as $edge) {
                $code .= sprintf(
                    "  place_%s -> place_%s [label=\"%s\" style=\"%s\"%s];\n",
                    $this->dotize($id),
                    $this->dotize($edge['to']),
                    $this->escape($edge['name']),
                    'solid',
                    $this->addAttributes($edge['attributes'])
                );
            }
        }

        return $code;
    }
}
