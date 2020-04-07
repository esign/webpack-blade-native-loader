<?php
/**
 * Created by Bart Decorte
 * Date: 06/04/2020
 * Time: 22:34
 */

$opts = getopt('', ['source:', 'out:']);
$source = file_get_contents($opts['source']);

$matches = [];
// Illuminate\View\Compilers\BladeCompiler:compileStatements() line 407
preg_replace_callback(
    '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) use (&$matches) {
    $matches[] = $match;
    return;
}, $source);

$matches = array_filter($matches, function ($match) {
    return count($match) >= 5;
});
$matches = array_filter($matches, function ($match) {
    return in_array($match[1], ['extends', 'include', 'component']);
});
$matches = array_map(function ($match) {
    return trim(explode(',', $match[4])[0], '\'"');
}, $matches);
$matches = array_map(function ($match) {
    return str_replace('.', '/', $match);
}, $matches);

$handle = fopen($opts['out'], 'w');
fwrite($handle, json_encode(array_values($matches)));
fclose($handle);
exit();
