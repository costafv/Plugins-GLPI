<?php
/**
 * Hooks for flvc.custom plugin.
 */

if (!defined('GLPI_ROOT')) {
    die('Sorry. You cannot access this file directly.');
}

require_once __DIR__ . '/inc/autoload.php';

function plugin_flvccustom_display_login(array $params = []): bool
{
    return PluginFlvcCustomLoginManager::display();
}
