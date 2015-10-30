<?php

$recipe = new \Soy\Recipe();

$recipe->prepare(\Soy\Task\GulpTask::class, function () {
    $gulpTask = new \Soy\Task\GulpTask();
    $gulpTask->setBinary('gulp');
    return $gulpTask;
});

$recipe->component('default', null, ['gulp']);

$recipe->component('gulp', function (\Soy\Task\GulpTask $gulp) {
    $gulp->run();
});

return $recipe;
