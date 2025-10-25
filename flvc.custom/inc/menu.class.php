<?php
/**
 * Custom menu entry for flvc.custom configuration.
 */

class PluginFlvcCustomMenu extends CommonGLPI
{
    public static function getMenuName(): string
    {
        return __('flvc.custom - Layouts', 'flvccustom');
    }

    public static function getMenuContent(): array
    {
        return [
            'title'   => self::getMenuName(),
            'page'    => '/plugins/flvc.custom/front/config.form.php',
            'default' => '/plugins/flvc.custom/front/config.form.php',
            'links'   => [
                'search' => null,
                'add'    => '/plugins/flvc.custom/front/config.form.php',
            ],
        ];
    }
}
