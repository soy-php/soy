<?php

namespace Soy\Task;

use League\CLImate\CLImate;
use Soy\Exception\CliTaskException;

abstract class AbstractCliTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $binary;

    /**
     * @var bool
     */
    protected $verbose = false;

    /**
     * @var CLImate
     */
    protected $climate;

    /**
     * @var bool
     */
    protected $throwExceptionOnError = true;

    /**
     * @param CLImate $climate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    public function run()
    {
        $binary = $this->getBinary();

        if ($this->isVerbose()) {
            $this->climate->lightBlue('$ ' . $binary);
        }

        exec($binary, $output, $exitCode);

        if ($this->isVerbose()) {
            $this->climate->dim(implode(PHP_EOL, $output));
        }

        if ($exitCode !== 0 && $this->shouldThrowExceptionOnError()) {
            throw new CliTaskException('Non-zero exit code: ' . $exitCode);
        }
    }

    /**
     * @param string $binary
     * @return $this
     */
    public function setBinary($binary)
    {
        $this->binary = $binary;
        return $this;
    }

    /**
     * @return string
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param bool $verbose
     * @return $this
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldThrowExceptionOnError()
    {
        return $this->throwExceptionOnError;
    }

    /**
     * @param bool $throwExceptionOnError
     * @return $this
     */
    public function setThrowExceptionOnError($throwExceptionOnError)
    {
        $this->throwExceptionOnError = $throwExceptionOnError;
        return $this;
    }
}
