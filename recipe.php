<?php

$recipe = new \Soy\Recipe();

$recipe->prepare(\League\CLImate\CLImate::class, function (\League\CLImate\CLImate $climate) {
    $climate->arguments->add('verbose', [
        'prefix' > 'v',
        'longPrefix' > 'verbose',
        'description' > 'Verbose output',
        'noValue' > true,
    ]);

    return $climate;
});

$recipe->prepare(\Soy\Task\GulpTask::class, function () {
    $gulpTask = new \Soy\Task\GulpTask();
    $gulpTask->setBinary('gulp');
    return $gulpTask;
});

$recipe->component('default', null, ['gulp']);

$recipe->component('gulp', function (\Soy\Task\GulpTask $gulp, \League\CLImate\CLImate $climate) {
    if ($climate->arguments->defined('verbose')) {
        $climate->green('Running gulp');
    }
    $gulp->run();
});

return $recipe;
