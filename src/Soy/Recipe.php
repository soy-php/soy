<?php

namespace Soy;

use League\CLImate\CLImate;
use Soy\Exception\UnknownComponentException;

class Recipe
{
    /**
     * @var array
     */
    private $preparations = [];

    /**
     * @var Component[]
     */
    private $components = [];

    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * @param string $class
     * @param callable $callable
     * @param bool $prepend
     */
    public function prepare($class, callable $callable, $prepend = false)
    {
        if (!isset($this->preparations[$class])) {
            $this->preparations[$class] = [];
        }

        if ($prepend) {
            array_unshift($this->preparations[$class], $callable);
        } else {
            array_push($this->preparations[$class], $callable);
        }
    }

    /**
     * @param string $component
     * @param callable|null $callable
     * @param array $dependencies
     * @return Component
     */
    public function component($component, callable $callable = null, array $dependencies = [])
    {
        $this->components[$component] = new Component($component, $callable);
        $this->dependencies[$component] = $dependencies;

        return $this->components[$component];
    }

    /**
     * @return Component[]
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param string $componentName
     * @return Component
     * @throws UnknownComponentException
     */
    public function getComponent($componentName)
    {
        if (!array_key_exists($componentName, $this->components)) {
            throw new UnknownComponentException('Component ' . $componentName . ' not found');
        }

        return $this->components[$componentName];
    }

    /**
     * @return array
     */
    public function getPreparations()
    {
        return $this->preparations;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function cli(callable $callable)
    {
        $this->prepare(CLImate::class, function (CLImate $climate) use ($callable) {
            $callable($climate);
            return $climate;
        });

        return $this;
    }
}
