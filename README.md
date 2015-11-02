# Soy

## Introduction
Soy is a PHP task runner focused on clean syntax and allowing flexible implementation.
This project is still under heavy development so there's no tag yet.

## Tasks
- [Replace Task](https://github.com/soy-php/replace-task)
- [Gulp Task](https://github.com/soy-php/gulp-task)
- [Grunt Task](https://github.com/soy-php/grunt-task)
- [PHP Code Sniffer](https://github.com/soy-php/codesniffer-task)

## Usage
Include soy in your project with composer:

```sh
$ composer require soy-php/soy=dev-develop@dev
```

Include the tasks you need using composer, Soy doesn't come with any default tasks.
For this example we can include the Gulp Task:

```sh
$ composer require soy-php/gulp-task=dev-develop@dev
```

Then create a `recipe.php` in your project's directory and put your tasks in there:

```php
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
    $gulpTask->setBinary('/usr/local/bin/gulp');
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
```

## API
The core of Soy is the Recipe, it has two main methods: `prepare` and `component`.

### Prepare
Prepare can be used to setup defaults for any of the tasks, the first parameter is the class name and the second
parameter is a closure which accepts an instance of the class as a first parameter.
Make sure you always return the object.

```php
$recipe->prepare(\Soy\Task\GulpTask::class, function (\Soy\Task\GulpTask $gulpTask) {
    $gulpTask->setBinary('/usr/local/bin/gulp');
    return $gulpTask;
});
```

The `prepare` method also allows you to add accepted arguments using [CLImate](http://climate.thephpleague.com/).
For example:

```php
$recipe->prepare(\League\CLImate\CLImate::class, function (\League\CLImate\CLImate $climate) {
    $climate->arguments->add('verbose', [
        'prefix' => 'v',
        'longPrefix' => 'verbose',
        'description' => 'Verbose output',
        'noValue' => true,
    ]);

    return $climate;
});
```

You can have as many `prepare` methods as you want and even for the same class. You can also pass a third parameter
to prepend the preparation instead of appending it.

### Component
Component can be used to execute your tasks, the first parameter is the name of the component, the second
parameter is a closure (or null) to execute and the third parameter is an array of dependencies on other components.

```php
$recipe->component('gulp', function (\Soy\Task\GulpTask $gulpTask, \League\CLImate\CLImate $climate) {
    $verbose = $climate->arguments->defined('verbose');
    if ($verbose) {
        $climate->green('Running gulp');
    }

    $gulpTask
        ->setVerbose($verbose)
        ->run();
});
```

You can put anything in the signature of the closure, the corresponding objects will be injected based on the type-hint.
