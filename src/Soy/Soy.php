<?php

namespace Soy;

use DI\Container;
use DI\ContainerBuilder;
use Soy\Exception\UnknownComponentException;

class Soy
{
    const VERSION = '0.1.0';

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
     * @throws UnknownComponentException
     */
    public function execute($component = 'default')
    {
        $container = $this->getContainer();

        $components = $this->recipe->getComponents();
        $dependencies = $this->recipe->getDependencies();

        if (!array_key_exists($component, $components)) {
            throw new UnknownComponentException('Unknown component: ' . $component, $component);
        }

        $componentDependencies = $dependencies[$component];
        foreach ($componentDependencies as $dependency) {
            $this->execute($dependency);
        }

        $callable = $components[$component];
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
