<?php

namespace Soy;

use League\CLImate\CLImate;

class Cli
{
    /**
     * @var Soy
     */
    private $soy;

    /**
     * @param Soy $soy
     */
    public function __construct(Soy $soy)
    {
        $this->soy = $soy;
        $this->soy->getRecipe()->prepare(CLImate::class, function (CLImate $climate) {
            $climate->arguments->add([
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

            return $climate;
        }, true);
    }

    /**
     * @param array $arguments
     */
    public function handle(array $arguments)
    {
        $this->soy->prepare();

        $climate = $this->soy->getContainer()->get(CLImate::class);
        $climate->arguments->parse($arguments);

        if ($climate->arguments->defined('help')) {
            $climate->usage();
            die;
        }

        $component = $climate->arguments->get('component');

        $this->soy->execute($component);
    }
}
