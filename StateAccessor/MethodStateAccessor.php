<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Workflow\StateAccessor;

final class MethodStateAccessor implements StateAccessorInterface
{
    /**
     * @var string
     */
    private $getterName;

    /**
     * @var string
     */
    private $setterName;

    /**
     * @param string $getterName
     * @param string $setterName
     */
    public function __construct(string $getterName, string $setterName)
    {
        $this->getterName = $getterName;
        $this->setterName = $setterName;
    }

    /**
     * @param string $property
     * @return MethodStateAccessor
     */
    public static function fromProperty(string $property): MethodStateAccessor
    {
        $getterName = 'get'.ucfirst($property);
        $setterName = 'set'.ucfirst($property);
        return new self($getterName, $setterName);
    }

    /**
     * @param object $subject
     * @return string
     */
    public function getState(object $subject): string
    {
        if (!method_exists($subject, $this->getterName)) {
            throw new LogicException(sprintf('The method "%s::%s()" does not exist.', \get_class($subject), $Method));
        }

        return $subject->{$this->getterName}();
    }

    /**
     * @param object $subject
     * @param string $state
     * @param array $context
     */
    public function setState(object $subject, string $state, array $context = []): void
    {
        if (!method_exists($subject, $this->setterName)) {
            throw new LogicException(sprintf('The method "%s::%s()" does not exist.', \get_class($subject), $method));
        }

        $subject->{$this->setterName}($state, $context);
    }
}
