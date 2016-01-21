<?php

namespace Soy;

use Exception;
use League\CLImate\CLImate;
use Soy\Exception\CliArgumentDuplicationException;
use Soy\Exception\Diagnoser;
use Soy\Exception\FatalErrorException;
use Soy\Exception\NoRecipeReturnedException;
use Soy\Exception\RecipeFileNotFoundException;

class Cli
{
    const DEFAULT_RECIPE_FILE = 'recipe.php';

    /**
     * @var array
     */
    private $defaultArguments = [
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
        'recipe' => [
            'description' => 'The recipe file to use',
            'longPrefix' => 'recipe',
            'defaultValue' => self::DEFAULT_RECIPE_FILE,
        ]
    ];

    public function handle()
    {
        $soy = $this->bootstrap();

        $defaultArguments = $this->defaultArguments;

        $soy->getRecipe()->prepare(CLImate::class, function (CLImate $climate) use ($defaultArguments) {
            $climate->arguments->add($defaultArguments);
            return $climate;
        }, true);

        $soy->prepare();

        $container = $soy->getContainer();

        /** @var CLImate $climate */
        $climate = $container->get(CLImate::class);
        $climate->arguments->parse();

        $componentName = $climate->arguments->get('component');

        $soy->prepareCli($climate);
        $this->validateCli($climate);

        if ($climate->arguments->defined('help')) {
            $climate->green(sprintf('Soy version %s by @rskuipers', Soy::VERSION));
            $climate->usage();
            exit;
        }

        $soy->execute($componentName);
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

    /**
     * @return Soy
     * @throws Exception
     * @throws NoRecipeReturnedException
     * @throws RecipeFileNotFoundException
     */
    private function bootstrap()
    {
        $climate = new CLImate();
        $climate->arguments->add($this->defaultArguments);
        $climate->arguments->parse();

        if ($climate->arguments->defined('version')) {
            $climate->out(Soy::VERSION);
            exit;
        }

        if (!$climate->arguments->defined('noDiagnostics')) {
            $this->registerErrorHandlers();
        }

        $recipeFile = $climate->arguments->get('recipe');
        if (!is_file($recipeFile)) {
            throw new RecipeFileNotFoundException('Recipe file not found at path ' . $recipeFile, $recipeFile);
        }

        chdir(dirname($recipeFile));

        $recipe = include_once basename($recipeFile);

        if (!$recipe instanceof Recipe) {
            throw new NoRecipeReturnedException('No recipe returned in file ' . realpath($recipeFile));
        }

        return new Soy($recipe);
    }

    private function validateCli(CLImate $climate)
    {
        $longPrefixes = [];
        $prefixes = [];
        $names = [];

        $arguments = $climate->arguments->all();
        foreach ($arguments as $argument) {
            if (in_array($argument->name(), $names)) {
                throw new CliArgumentDuplicationException('Duplicate name: ' . $argument->name());
            }
            $names[] = $argument->name();

            if (in_array($argument->longPrefix(), $longPrefixes)) {
                throw new CliArgumentDuplicationException('Duplicate longPrefix: ' . $argument->longPrefix());
            }
            $longPrefixes[] = $argument->longPrefix();

            if ($argument->prefix() && in_array($argument->prefix(), $prefixes)) {
                throw new CliArgumentDuplicationException('Duplicate prefix: ' . $argument->prefix());
            }
            $prefixes[] = $argument->prefix();
        }
    }
}
