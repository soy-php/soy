<?php

namespace Soy\Task;

abstract class AbstractCliTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $binary;

    public function run()
    {
        passthru($this->binary);
    }

    /**
     * @param string $binary
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;
    }
}
