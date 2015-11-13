<?php

namespace Soy\Task;

use League\CLImate\CLImate;
use Soy\Exception\CliTaskException;

class CliTask implements TaskInterface
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
     * @var array
     */
    protected $arguments = [];

    /**
     * @param CLImate $climate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    public function run()
    {
        $command = $this->getCommand();

        if ($this->isVerbose()) {
            $this->climate->lightBlue('$ ' . $command);
        }

        exec($command, $output, $exitCode);

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
     * @return string
     */
    public function getCommand()
    {
        $command = $this->getBinary();

        if (count($this->getArguments()) > 0) {
            $command .= ' ' . implode(' ', $this->getArguments());
        }

        return $command;
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

    /**
     * @param string $argument
     * @return $this
     */
    public function addArgument($argument)
    {
        $this->arguments[] = $argument;
        return $this;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
