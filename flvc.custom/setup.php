<?php
/**
 * flvc.custom plugin bootstrap for GLPI 11.x.
 */

if (!defined('GLPI_ROOT')) {
    die('Sorry. You cannot access this file directly.');
}

const PLUGIN_FLVCUSTOM_MIN_VERSION = '11.0.0';
const PLUGIN_FLVCUSTOM_MAX_VERSION = '11.0.99';

require_once __DIR__ . '/inc/autoload.php';

function plugin_init_flvccustom(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['flvccustom'] = true;
    $PLUGIN_HOOKS['config_page']['flvccustom']   = 'front/config.form.php';
    $PLUGIN_HOOKS['menu_toadd']['flvccustom']    = ['tools' => 'PluginFlvcCustomMenu'];
    $PLUGIN_HOOKS['display_login']['flvccustom'] = [PluginFlvcCustomLoginManager::class, 'display'];
}

function plugin_version_flvccustom(): array
{
    return [
        'name'           => __('flvc.custom', 'flvccustom'),
        'version'        => '1.0.0',
        'author'         => 'OpenAI Assistant',
        'homepage'       => 'https://glpi-project.org',
        'license'        => 'GPLv2+',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_FLVCUSTOM_MIN_VERSION,
                'max' => PLUGIN_FLVCUSTOM_MAX_VERSION,
            ],
        ],
        'minGlpiVersion' => PLUGIN_FLVCUSTOM_MIN_VERSION,
    ];
}

function plugin_flvccustom_check_prerequisites(): bool
{
    if (version_compare(GLPI_VERSION, PLUGIN_FLVCUSTOM_MIN_VERSION, '<')) {
        echo __('O plugin flvc.custom requer o GLPI 11 ou superior.', 'flvccustom');
        return false;
    }

    return true;
}

function plugin_flvccustom_check_config(bool $verbose = false): bool
{
    return PluginFlvcCustomConfig::check($verbose);
}

function plugin_flvccustom_install(): bool
{
    return PluginFlvcCustomConfig::install();
}

function plugin_flvccustom_uninstall(): bool
{
    return PluginFlvcCustomConfig::uninstall();
}
