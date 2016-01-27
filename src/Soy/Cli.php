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
     * @var string
     */
    private $recipe;

    /**
     * @var bool
     */
    private $useCwd;

    /**
     * @var bool
     */
    private $selfContainedRecipe;

    /**
     * @var array
     */
    private static $defaultArguments = [
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
        ],
    ];

    public function handle()
    {
        $soy = $this->bootstrap();

        $defaultArguments = static::$defaultArguments;

        if ($this->isSelfContainedRecipe()) {
            unset($defaultArguments['recipe']);
        }

        $soy->getRecipe()->prepare(CLImate::class, function (CLImate $climate) use ($defaultArguments) {
            $climate->arguments->add($defaultArguments);
            return $climate;
        }, true);

        $soy->prepare();

        $container = $soy->getContainer();

        /** @var CLImate $climate */
        $climate = $container->get(CLImate::class);
        $climate->arguments->parse();

        $componentName = $this->parseComponentFromCli($climate);

        $soy->prepareCli($climate, $componentName);
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
        $climate->arguments->add(static::$defaultArguments);
        $climate->arguments->parse();

        if ($climate->arguments->defined('version')) {
            $climate->out(Soy::VERSION);
            exit;
        }

        if (!$climate->arguments->defined('noDiagnostics')) {
            $this->registerErrorHandlers();
        }

        $recipePath = $this->recipe;

        // If not self-contained get the recipe from CLI command
        if (!$this->isSelfContainedRecipe()) {
            $this->recipe = $climate->arguments->get('recipe');
            $recipePath = basename($this->recipe);
        }

        if (!is_file($recipePath)) {
            throw new RecipeFileNotFoundException('Recipe file not found at path ' . $this->recipe, $this->recipe);
        }

        if (!$this->isUseCwd()) {
            chdir(dirname($this->recipe));
        }

        $recipe = include_once $recipePath;

        if (! $recipe instanceof Recipe) {
            throw new NoRecipeReturnedException('No recipe returned in file ' . realpath($this->recipe));
        }

        return new Soy($recipe);
    }

    private function isSelfContainedRecipe()
    {
        return $this->selfContainedRecipe;
    }

    /**
     * @return boolean
     */
    private function isUseCwd()
    {
        return $this->useCwd;
    }

    /**
     * Self Contained Recipes disables the option to change the recipe during and multiple components, only relying
     * on the default one. Also makes possible to use de current working directory instead of the recipe's base one
     * @param string $recipe the recipe.php path
     */
    public function setSelfContainedRecipe($recipe)
    {
        $this->recipe = $recipe;
        $this->useCwd = true;
        $this->selfContainedRecipe = true;
    }

    /**
     * @param CLImate $climate
     * @throws CliArgumentDuplicationException
     */
    private function validateCli(CLImate $climate)
    {
        $longPrefixes = [];
        $prefixes = [];

        $arguments = $climate->arguments->all();
        foreach ($arguments as $argument) {
            if (in_array($argument->longPrefix(), $longPrefixes, true)) {
                throw new CliArgumentDuplicationException('Duplicate longPrefix: ' . $argument->longPrefix());
            }
            $longPrefixes[] = $argument->longPrefix();

            if ($argument->prefix() && in_array($argument->prefix(), $prefixes, true)) {
                throw new CliArgumentDuplicationException('Duplicate prefix: ' . $argument->prefix());
            }
            $prefixes[] = $argument->prefix();
        }
    }

    /**
     * @param CLImate $climate
     * @return string
     */
    private function parseComponentFromCli(CLImate $climate)
    {
        $componentName = $climate->arguments->get('component');

        if (strpos($componentName, '-') === 0) {
            $componentName = $climate->arguments->all()['component']->defaultValue();
        }

        return $componentName;
    }
}
