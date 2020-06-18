<?php
/**
 * Created by Bart Decorte
 * Date: 18/06/2020
 * Time: 21:10
 */
require __DIR__ . '/vendor/autoload.php';

use \Illuminate\View\Factory;
use \Illuminate\View\Engines\EngineResolver;
use \Illuminate\View\FileViewFinder;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\Events\Dispatcher;
use \Illuminate\View\Engines\CompilerEngine;
use \Illuminate\Container\Container;
use \App\BladeCompiler;

if (!file_exists($cachePath)) {
    mkdir($cachePath);
}

// Set up the ViewFactory
$files = new Filesystem();
$compiler = new BladeCompiler($files, $cachePath);
$compilerEngine = new CompilerEngine($compiler);
$engines = new EngineResolver();
$engines->register('blade', function () use ($compilerEngine) {
    return $compilerEngine;
});
$paths = [$viewPath];
$container = new Container();
Container::setInstance($container);
$finder = new FileViewFinder($files, $paths);
$events = new Dispatcher($container);
$factory = new Factory($engines, $finder, $events);
$factory->setContainer($container);
$container->singleton(\Illuminate\Contracts\View\Factory::class, function ($app) use ($factory) {
    return $factory;
});
$container->alias(\Illuminate\Contracts\View\Factory::class, 'view');
