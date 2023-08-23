<?php
/**
 * Created by Bart Decorte
 * Date: 28/03/2020
 * Time: 11:53
 */

$opts = getopt('', ['view-dir:', 'source:', 'out:']);
$viewDir = trim($opts['view-dir'], '/');
$viewPath = __DIR__ . '/../../' . $viewDir;
$source = file_get_contents($opts['source']);
$cachePath = __DIR__ . '/cache';

// Write template source to a random temporary file
$currentViewFilename = '__' . md5(rand(0, 999999));
while (file_exists($currentViewPath = "$viewPath/$currentViewFilename.blade.php")) {
    $currentViewFilename = '__' . md5(rand(0, 999999));
}

file_put_contents($currentViewPath, $source);

require __DIR__ . '/factory.php';

// Render the template contained in the temporary file created above
try {
    $compiled = $factory->make($currentViewFilename)->render();
} finally {
    // Clean-up, always, even if an error occurred
    unlink($currentViewPath);
}

// Write the compilation result
$handle = fopen($opts['out'], 'w');
fwrite($handle, $compiled);
fclose($handle);
exit();
