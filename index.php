<?php
/**
 * Created by Bart Decorte
 * Date: 28/03/2020
 * Time: 11:53
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

$opts = getopt('', ['view-dir:', 'source:', 'out:']);
$viewDir = trim($opts['view-dir'], '/');
$viewPath = __DIR__ . '/../../' . $viewDir;
$source = file_get_contents($opts['source']);
$cachePath = __DIR__ . '/cache';

if (!file_exists($cachePath)) {
    mkdir(__DIR__ . '/cache');
}

$currentViewFilename = '__' . md5(rand(0, 999999));
while (file_exists($currentViewPath = "$viewPath/$currentViewFilename.blade.php")) {
    $currentViewFilename = '__' . md5(rand(0, 999999));
}

file_put_contents($currentViewPath, $source);

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

try {
    $compiled = $factory->make($currentViewFilename)->render();
} finally {
    unlink($currentViewPath);
}

$handle = fopen($opts['out'], 'w');
fwrite($handle, $compiled);
fclose($handle);
exit();
