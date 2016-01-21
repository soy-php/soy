# Soy

[![Latest Stable Version](https://poser.pugx.org/soy-php/soy/v/stable)](https://packagist.org/packages/soy-php/soy) [![Total Downloads](https://poser.pugx.org/soy-php/soy/downloads)](https://packagist.org/packages/soy-php/soy) [![Latest Unstable Version](https://poser.pugx.org/soy-php/soy/v/unstable)](https://packagist.org/packages/soy-php/soy) [![License](https://poser.pugx.org/soy-php/soy/license)](https://packagist.org/packages/soy-php/soy)

## Introduction
Soy is a PHP task runner focused on clean syntax and allowing flexible implementation.

For more information, see the [Why Soy?](#why-soy) section.

## Tasks
- [Codeception](https://github.com/soy-php/codeception-task)
- [Doctrine Migrations](https://github.com/soy-php/doctrine-migrations-task)
- [Grunt](https://github.com/soy-php/grunt-task)
- [Gulp](https://github.com/soy-php/gulp-task)
- [Phinx](https://github.com/soy-php/phinx-task)
- [PHP Code Sniffer](https://github.com/soy-php/phpcs-task)
- [PHP Lint](https://github.com/soy-php/php-lint-task)
- [PHP Mess Detector](https://github.com/soy-php/phpmd-task)
- [Replace](https://github.com/soy-php/replace-task)

## Usage
Include soy in your project with composer:

```sh
$ composer require soy-php/soy
```

Include the tasks you need using composer, Soy doesn't come with any default tasks.
For this example we can include the Gulp Task:

```sh
$ composer require soy-php/gulp-task
```

Then create a `recipe.php` in your project's directory and put your tasks in there.
This is the simplest example:

```php
<?php

$recipe = new \Soy\Recipe();

$recipe->component('default', function (\Soy\Task\GulpTask $gulpTask) {
    $gulpTask->run();
});

return $recipe;
```

This is a more advanced example using custom CLI arguments and such:

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
    return $gulpTask->setBinary('/usr/local/bin/gulp');
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
    return $gulpTask->setBinary('/usr/local/bin/gulp');
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

### CLI
There are three ways of defining CLI commands, each suitable for different situations.

If you want to introduce a global argument:

```php
$recipe->cli(function (\League\CLImate\CLImate $climate) {
    $climate->arguments->add([
        'foo' => [
            'description' => 'foo',
            'longPrefix' => 'foo',
            'noValue' => true,
        ],
    ]);
});
```

If you want to add arguments from a specific task to your component:

```php
$fooComponent = $recipe->component('foo', function (\Soy\Task\FooTask $fooTask, \Soy\Task\BarTask $barTask) {
    $fooTask->run();
    $barTask->run();
});

$fooComponent->cli([\Soy\Task\FooTask::class, 'prepareCli']);
$fooComponent->cli([\Soy\Task\BarTask::class, 'prepareCli']);
```

You can also use fluent interfacing:

```php
$recipe->component('foo', function (\Soy\Task\FooTask $fooTask, \Soy\Task\BarTask $barTask) {
    $fooTask->run();
    $barTask->run();
})
    ->cli([\Soy\Task\FooTask::class, 'prepareCli'])
    ->cli([\Soy\Task\BarTask::class, 'prepareCli'])
;
```

If you want to add your own component specific arguments:

```php
$fooComponent = $recipe->component('foo', function (\Soy\Task\FooTask $fooTask) {
    $fooTask->run();
});

$fooComponent->cli(function (\League\CLImate\CLImate $climate) {
    $climate->arguments->add([
        'foo' => [
            'description' => 'foo',
            'longPrefix' => 'foo',
            'noValue' => true,
        ],
    ]);
});
```

## Why Soy?
Soy's focus is to give power back to the developer.

### PHP
Soy's recipes are written in plain PHP, no new language you have to familiarize yourself with, nor are we forcing
you to use a markup language. The result of this decision is that you are no longer limited in what you can do.

### Tasks
Soy's tasks are mostly CLI wrappers, every task has at least one `run()` method that doesn't accept any arguments.
That means tasks are focused on doing one thing and all options are passed through setters. Because the tasks are
CLI wrappers, there's no risk of strange integration bugs and debugging becomes easy.

### Reusability
Reusability is something we all strive for when developing code, Soy has two ways to reinforce that mindset.

The first pillar to support this is the concept of [preparing](#prepare) a task. Task preparations are stacked and 
can be either appended or prepended. These preparations will be run during the bootstrap phase of Soy, allowing you
to manipulate any task. An interesting object you can prepare is CLImate, preparing CLImate allows you to add
required/optional arguments/flags, giving you full control over how you interact with your task runner.

The second pillar in supporting reusability is picking setters overs `run()` method arguments, every task accepts
its options through setters allowing you to set defaults in your task preparations. You could create a recipe with sane
defaults, require it in your project's recipe and customize only a few things like file paths and such.

### Output
Soy doesn't like to talk, it leaves the talking to you. You can enable verbose mode on CLI tasks to get some more
insights on the command line used and the output of the command, but there's no default output when a component gets
executed. You can add [CLImate](http://climate.thephpleague.com/) as an argument in your component and use its awesome
output functions to your own likings.
