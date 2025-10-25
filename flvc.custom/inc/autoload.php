<?php
/**
 * Simple autoloader for flvc.custom classes.
 */

spl_autoload_register(static function (string $class): void {
    $prefix = 'PluginFlvcCustom';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $relative = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $relative));
    $relative = str_replace('_', '/', $relative);

    $path = __DIR__ . '/' . $relative . '.class.php';

    if (is_readable($path)) {
        require_once $path;
    }
});
