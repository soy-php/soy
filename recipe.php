<?php

$recipe = new \Soy\Recipe();

$recipe->prepare(\League\CLImate\CLImate::class, function (\League\CLImate\CLImate $climate) {
    $climate->arguments->add('verbose', [
        'prefix' => 'v',
        'longPrefix' => 'verbose',
        'description' => 'Verbose output',
        'noValue' => true,
    ]);

    return $climate;
});

$recipe->prepare(\Soy\Task\GulpTask::class, function (\Soy\Task\GulpTask $gulpTask) {
    $gulpTask->setBinary('ls -hal');
    return $gulpTask;
});

$recipe->component('gulp', function (\Soy\Task\GulpTask $gulpTask, \League\CLImate\CLImate $climate) {
    $verbose = $climate->arguments->defined('verbose');
    if ($verbose) {
        $climate->green('Running gulp');
    }

    $gulpTask
        ->setVerbose($verbose)
        ->run();
});

$recipe->component('default', null, ['gulp']);

return $recipe;
