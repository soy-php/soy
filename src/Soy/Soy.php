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

    public function prepare()
    {
        $containerBuilder = new ContainerBuilder();

        foreach ($this->recipe->getPreparations() as $class => $callables) {
            foreach ($callables as $callable) {
                $containerBuilder->addDefinitions([
                    $class => \DI\decorate($callable)
                ]);
            }
        }

        $this->container = $containerBuilder->build();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        if ($this->container === null) {
            throw new \LogicException('Recipe is not prepared');
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
