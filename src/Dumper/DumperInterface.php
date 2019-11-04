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

/**
 * DumperInterface is the interface implemented by workflow dumper classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
interface DumperInterface
{
    /**
     * Dumps a workflow definition.
     *
     * @param Definition $definition
     * @param string|null $state
     * @param array $options
     * @return string The representation of the workflow
     */
    public function dump(Definition $definition, ?string $state = null, array $options = []);
}
