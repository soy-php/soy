<?php

namespace Soy;

class Recipe
{
    /**
     * @var array
     */
    private $preparations;

    /**
     * @var array
     */
    private $components = [];

    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * @param string $class
     * @param callable $callable
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
     */
    public function component($component, callable $callable = null, $dependencies = [])
    {
        $this->components[$component] = $callable;
        $this->dependencies[$component] = $dependencies;
    }

    /**
     * @return array
     */
    public function getComponents()
    {
        return $this->components;
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
}
