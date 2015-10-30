<?php

namespace Soy;

use DI\Container;
use DI\ContainerBuilder;

class Soy
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Recipe
     */
    private $recipe;

    /**
     * @param Recipe $recipe
     */
    public function __construct(Recipe $recipe)
    {
        $this->recipe = $recipe;
    }

    /**
     * @param string $component
     */
    public function execute($component = 'default')
    {
        $container = $this->getContainer();

        $dependencies = $this->recipe->getDependencies()[$component];
        foreach ($dependencies as $dependency) {
            $this->execute($dependency);
        }

        $callable = $this->recipe->getComponents()[$component];
        if (is_callable($callable)) {
            $container->call($callable);
        }
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        if ($this->container === null) {
            $definitions = [];

            foreach ($this->recipe->getPreparations() as $class => $callable) {
                $definitions[$class] = $callable;
            }

            $containerBuilder = new ContainerBuilder();
            $containerBuilder->addDefinitions($definitions);

            $this->container = $containerBuilder->build();
        }

        return $this->container;
    }

    /**
     * @return Recipe
     */
    public function getRecipe()
    {
        return $this->recipe;
    }
}
