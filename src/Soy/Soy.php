<?php

namespace Soy;

use DI\Container;
use DI\ContainerBuilder;
use League\CLImate\CLImate;
use Soy\Exception\UnknownComponentException;

class Soy
{
    const VERSION = '0.2.0';

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
     * @param string $componentName
     * @throws UnknownComponentException
     */
    public function execute($componentName = 'default')
    {
        $container = $this->getContainer();

        $this->traverseDependencies($componentName, function ($dependency) {
            $this->execute($dependency);
        });

        $this->getRecipe()->getComponent($componentName)->execute($container);
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

    /**
     * @param CLImate $climate
     * @param string $componentName
     * @throws UnknownComponentException
     */
    public function prepareCli(CLImate $climate, $componentName)
    {
        $component = $this->getRecipe()->getComponent($componentName);

        $this->traverseDependencies($componentName, function ($componentName) use ($climate) {
            $this->getRecipe()->getComponent($componentName)->prepareCli($climate);
        });

        $component->prepareCli($climate);

        $climate->arguments->parse();
    }

    /**
     * @param string $componentName
     * @param callable $callable
     */
    private function traverseDependencies($componentName, callable $callable)
    {
        array_walk($this->recipe->getDependencies()[$componentName], function ($dependency) use ($callable) {
            $callable($dependency);
            $this->traverseDependencies($dependency, $callable);
        });
    }
}
