<?php
/**
 * Created by Bart Decorte
 * Date: 06/04/2020
 * Time: 22:34
 */

$opts = getopt('', ['view-dir:', 'source:', 'out:']);
$viewDir = trim($opts['view-dir'], '/');
$viewPath = __DIR__ . '/../../' . $viewDir;
$source = file_get_contents($opts['source']);
$cachePath = __DIR__ . '/cache';

require __DIR__ . '/factory.php';

$matches = [];
// Match Blade directives
// Illuminate\View\Compilers\BladeCompiler:compileStatements() line 407
preg_replace_callback(
    '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) use (&$matches) {
    $matches[] = $match;
    return;
}, $source);

$matches = array_filter($matches, function ($match) {
    return count($match) >= 5;
});
$views = array_filter($matches, function ($match) {
    return in_array($match[1], ['extends', 'include', 'component']);
});

// Extract & trim quotes
$views = array_map(function ($match) {
    return trim(explode(',', $match[4])[0], '\'"');
}, $views);

// Match Blade tags

// Match Blade tags (opening)
// Illuminate\View\Compilers\ComponentTagCompiler::compileOpeningTags() line 82
$pattern = "/<\s*x[-\:]([\w\-\:\.]*)(?<attributes>(?:\s+[\w\-:.@]+(=(?:\\\"[^\\\"]*\\\"|\'[^\']*\'|[^\'\\\"=<>]+))?)*\s*)(?<![\/=\-])>/x";
$openingTagMatches = [];
preg_match_all($pattern, $source, $openingTagMatches);
$openingTagMatches = $openingTagMatches[1];

// Match Blade tags (self-closing)
// Illuminate\View\Compilers\ComponentTagCompiler::compileSelfClosingTags() line 126
$pattern = "/<\s*x[-\:]([\w\-\:\.]*)\s*(?<attributes>(?:\s+[\w\-:.@]+(=(?:\\\"[^\\\"]*\\\"|\'[^\']*\'|[^\'\\\"=<>]+))?)*\s*)\/>/x";
$selfClosingTagMatches = [];
preg_match_all($pattern, $source, $selfClosingTagMatches);
$selfClosingTagMatches = $selfClosingTagMatches[1];

$tagMatches = array_merge($openingTagMatches, $selfClosingTagMatches);

$tagViews = array_map(function ($match) use ($factory) {
    if ($factory->exists($view = "components.{$match}")) {
        return $view;
    }
    return null;
}, $tagMatches);
$tagViews = array_filter($tagViews);

// Combine directives & tags
$views = array_merge($views, $tagViews);

// Replace dot syntax by slashes
$views = array_map(function ($match) {
    return str_replace('.', '/', $match);
}, $views);

$handle = fopen($opts['out'], 'w');
fwrite($handle, json_encode(array_values($views)));
fclose($handle);
exit();
