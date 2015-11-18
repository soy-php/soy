<?php

namespace Soy;

use Exception;
use League\CLImate\CLImate;
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

    /**
     * @param array $arguments
     */
    public function handle(array $arguments)
    {
        $soy = $this->bootstrap($arguments);

        $defaultArguments = $this->defaultArguments;

        $soy->getRecipe()->prepare(CLImate::class, function (CLImate $climate) use ($defaultArguments) {
            $climate->arguments->add($defaultArguments);
            return $climate;
        }, true);

        $soy->prepare();

        $container = $soy->getContainer();

        /** @var CLImate $climate */
        $climate = $container->get(CLImate::class);
        $climate->arguments->parse($arguments);

        if ($climate->arguments->defined('help')) {
            $climate->green(sprintf('Soy version %s by @rskuipers', Soy::VERSION));
            $climate->usage();
            exit;
        }

        $component = $climate->arguments->get('component');

        $soy->execute($component);
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
     * @param array $arguments
     * @return Soy
     * @throws Exception
     * @throws NoRecipeReturnedException
     * @throws RecipeFileNotFoundException
     */
    private function bootstrap(array $arguments)
    {
        $climate = new CLImate();
        $climate->arguments->add($this->defaultArguments);
        $climate->arguments->parse($arguments);

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

        $recipe = include_once $recipeFile;

        if (!$recipe instanceof Recipe) {
            throw new NoRecipeReturnedException('No recipe returned in file ' . realpath($recipeFile));
        }

        return new Soy($recipe);
    }
}
