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
use \Illuminate\View\Compilers\BladeCompiler;
use \Illuminate\View\Engines\CompilerEngine;

function createDir($dir) {
    $pieces = explode('/', $dir);
    for ($i = 0; $i < count($pieces); $i++) {
        if (!file_exists($path = __DIR__ . '/' . implode('/', array_slice($pieces, 0, $i + 1)))) {
            mkdir($path);
        }
    }
}

function copyRecursive($sourceDir, $destinationDir) {
    foreach (new DirectoryIterator($sourceDir) as $file_info) {
        $filename = $file_info->getFilename();

        if ($file_info->isDot()) {
            continue;
        }

        if ($file_info->isDir()) {
            mkdir("$destinationDir/$filename");
            copyRecursive($file_info->getRealPath(), "$destinationDir/$filename");
        } elseif ($file_info->isFile()) {
            copy($file_info->getRealPath(), "$destinationDir/$filename");
        }
    }
}

function removeRecursive($dir) {
    foreach (new DirectoryIterator($dir) as $file_info) {
        $filename = $file_info->getFilename();

        if ($file_info->isDot()) {
            continue;
        }

        if ($file_info->isDir()) {
            removeRecursive("$dir/$filename");
        } elseif ($file_info->isFile()) {
            unlink($file_info->getRealPath());
        }
    }
    rmdir("$dir");
}

$opts = getopt('', ['view-dir:', 'source:', 'out:']);
$viewDir = rtrim($opts['view-dir'], '/');
$viewPath = __DIR__ . '/../../' . $viewDir;
$source = file_get_contents($opts['source']);

createDir('tmp/views');
createDir('tmp/cache');
copyRecursive($viewPath, __DIR__ . '/tmp/views');

$currentViewPath = __DIR__ . '/tmp/views/__current__.blade.php';
file_put_contents($currentViewPath, $source);

// Set up the ViewFactory
$files = new Filesystem();
$cachePath = __DIR__ . '/tmp/cache';
$compiler = new BladeCompiler($files, $cachePath);
$compilerEngine = new CompilerEngine($compiler);
$engines = new EngineResolver();
$engines->register('blade', function () use ($compilerEngine) {
    return $compilerEngine;
});
$paths = [__DIR__ . '/tmp/views'];
$finder = new FileViewFinder($files, $paths);
$events = new Dispatcher();
$factory = new Factory($engines, $finder, $events);

try {
    $compiled = $factory->make('__current__')->render();
} catch (Exception $e) {
    removeRecursive(__DIR__ . '/tmp');
    throw $e;
}

removeRecursive(__DIR__ . '/tmp');

$handle = fopen($opts['out'], 'w');
fwrite($handle, $compiled);
fclose($handle);
exit();
