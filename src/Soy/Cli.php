<?php

namespace Soy;

use Exception;
use League\CLImate\CLImate;
use Soy\Exception\Diagnoser;
use Soy\Exception\FatalErrorException;

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
                ],
                'version' => [
                    'description' => 'Show version',
                    'longPrefix' => 'version',
                    'noValue' => true,
                ],
                'noDiagnostics' => [
                    'description' => 'Disable diagnostics',
                    'longPrefix' => 'no-diagnostics',
                    'noValue' => true,
                ],
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

        $container = $this->soy->getContainer();

        /** @var CLImate $climate */
        $climate = $container->get(CLImate::class);
        $climate->arguments->parse($arguments);

        if (!$climate->arguments->defined('noDiagnostics')) {
            $this->registerErrorHandlers();
        }

        if ($climate->arguments->defined('help')) {
            $climate->green(sprintf('Soy version %s by @rskuipers', Soy::VERSION));
            $climate->usage();
            die;
        }

        if ($climate->arguments->defined('version')) {
            $climate->out(Soy::VERSION);
            die;
        }

        $component = $climate->arguments->get('component');

        $this->soy->execute($component);
    }

    private function registerErrorHandlers()
    {
        set_exception_handler(function (Exception $exception) {
            Diagnoser::diagnose($exception);
        });

        register_shutdown_function(function () {
            $error = error_get_last();
            if (is_array($error)) {
                Diagnoser::diagnose(new FatalErrorException(
                    $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
                ));
            }
        });
    }
}
