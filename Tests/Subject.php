<?php

namespace Symfony\Component\Workflow\Tests;

final class Subject
{
    private $state;
    private $context;

    public function __construct($state = null)
    {
        $this->state = $state;
        $this->context = [];
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state, array $context = [])
    {
        $this->state = $state;
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
