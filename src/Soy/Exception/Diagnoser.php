<?php

namespace Soy\Exception;

use Exception;
use League\CLImate\CLImate;
use ReflectionException;

class Diagnoser
{
    const MESSAGE_DEFINE_COMPONENT = <<<'PHP'
<?php

$recipe->component('%s', function () {
    // ...
});
PHP;

    const MESSAGE_FORGOT_DEFAULT = <<<'PHP'
<?php

$recipe->component('default', null, ['my-component-1', 'my-component-2']);
PHP;

    const MESSAGE_CALL_DIFFERENT = '$ soy my-component';

    const MESSAGE_PREPARE_RETURN = <<<'PHP'
<?php

$recipe->prepare(%1$s::class, function(%1$s $task) {
    <underline>return</underline> $task->setFoo('bar');
});
PHP;

    const MESSAGE_RETURN_RECIPE = <<<'PHP'
<?php

$recipe = new Soy\Recipe();

// ...

return $recipe;
PHP;

    /**
     * @param Exception $exception
     */
    public static function diagnose(Exception $exception)
    {
        $climate = new CLImate();
        
        $climate->error(get_class($exception) . ': ' . $exception->getMessage());
        $climate->error($exception->getTraceAsString())->br();

        if ($exception instanceof UnknownComponentException) {
            if ($exception->getComponent() === 'default') {
                $climate->lightYellow('Did you forget the default component?');
                $climate->dim(self::MESSAGE_FORGOT_DEFAULT)->br();
            }

            $climate->lightYellow('Did you forget to define the component?');
            $climate->dim(sprintf(self::MESSAGE_DEFINE_COMPONENT, $exception->getComponent()))->br();

            $climate->lightYellow('Did you mean to call a different component?');
            $climate->dim(self::MESSAGE_CALL_DIFFERENT)->br();
        } elseif ($exception instanceof ReflectionException) {
            if (preg_match('/^Class .*Task does not exist$/', $exception->getMessage())) {
                $climate->lightYellow('Did you forget to include the task with composer?');
                $climate->dim('$ composer require vendor/my-task')->br();
            }
        } elseif ($exception instanceof FatalErrorException) {
            if (preg_match(
                '/^Argument \d+ passed to {closure}\(\) must be an instance of (.*), null given/',
                $exception->getMessage(),
                $matches
            )) {
                $climate->lightYellow('Did you forget to return the object in your preparation?');
                $climate->dim(sprintf(self::MESSAGE_PREPARE_RETURN, $matches[1]))->br();
            }
        } elseif ($exception instanceof NoRecipeReturnedException) {
            $climate->lightYellow('Did you forget to return the recipe object in your recipe.php?');
            $climate->dim(self::MESSAGE_RETURN_RECIPE)->br();
        }

        die;
    }
}