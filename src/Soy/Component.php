<?php

namespace Soy;

use DI\Container;
use League\CLImate\CLImate;

class Component
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $cliPreparations = [];

    /**
     * @param string $name
     * @param callable $callable
     */
    public function __construct($name, $callable)
    {
        $this->name = $name;
        $this->callable = $callable;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function cli(callable $callable)
    {
        $this->cliPreparations[] = $callable;
        return $this;
    }

    /**
     * @return callable[]
     */
    public function getCliPreparations()
    {
        return $this->cliPreparations;
    }

    /**
     * @param Container $container
     * @return callable
     */
    public function execute(Container $container)
    {
        $container->call($this->callable);
    }

    /**
     * @param CLImate $climate
     */
    public function prepareCli(CLImate $climate)
    {
        $cliPreparations = $this->getCliPreparations();
        foreach ($cliPreparations as $cliPreparation) {
            $cliPreparation($climate);
        }
    }
}
