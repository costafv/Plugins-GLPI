<?php

define('PLUGIN_TERMOS_VERSION', '1.0.0');

define('PLUGIN_TERMOS_MIN_GLPI', '11.0.0');

define('PLUGIN_TERMOS_ROOT', __DIR__);

function plugin_init_termos(): void {
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['termos'] = true;

    // Register new menu entry inside the Plugins menu
    $PLUGIN_HOOKS['menu_toadd']['termos'] = [
        'plugins' => 'plugin_termos_menu',
    ];
}

function plugin_version_termos(): array {
    return [
        'name'           => __('Termos', 'termos'),
        'version'        => PLUGIN_TERMOS_VERSION,
        'author'         => 'OpenAI Assistant',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://glpi-project.org',
        'minGlpiVersion' => PLUGIN_TERMOS_MIN_GLPI,
    ];
}

function plugin_termos_check_prerequisites(): bool {
    if (version_compare(GLPI_VERSION, PLUGIN_TERMOS_MIN_GLPI, '<')) {
        if (method_exists('Session', 'addMessageAfterRedirect')) {
            Session::addMessageAfterRedirect(
                sprintf(
                    __('O plugin Termos requer o GLPI %s ou superior.', 'termos'),
                    PLUGIN_TERMOS_MIN_GLPI
                ),
                false,
                ERROR
            );
        } else {
            echo sprintf(
                __('O plugin Termos requer o GLPI %s ou superior.', 'termos'),
                PLUGIN_TERMOS_MIN_GLPI
            );
        }
        return false;
    }
    return true;
}

function plugin_termos_check_config(bool $verbose = false): bool {
    return true;
}
