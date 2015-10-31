<?php

namespace Soy;

use League\CLImate\CLImate;

class Cli
{
    /**
     * @var CLImate
     */
    private $climate;

    /**
     * @var Soy
     */
    private $soy;

    /**
     * @param CLImate $climate
     * @param Soy $soy
     */
    public function __construct(CLImate $climate, Soy $soy)
    {
        $this->soy = $soy;
        $this->climate = $climate;

        $this->climate->arguments->add([
            'component' => [
                'description' => 'The component to run',
                'defaultValue' => 'default',
            ],
            'help' => [
                'description' => 'Show usage',
                'longPrefix' => 'help',
                'noValue' => true,
            ]
        ]);
    }

    /**
     * @param array $arguments
     */
    public function handle(array $arguments)
    {
        $climate = $this->climate;

        $climate->arguments->parse($arguments);

        if ($climate->arguments->defined('help')) {
            $climate->usage();
            die;
        }

        $component = $this->climate->arguments->get('component');

        $this->soy->getRecipe()->prepare(CLImate::class, function () use ($climate) {
            return $climate;
        });

        $this->soy->execute($component);
    }
}
