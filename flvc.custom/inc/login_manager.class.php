<?php
/**
 * Login page rendering logic for flvc.custom.
 */

class PluginFlvcCustomLoginManager
{
    private static bool $rendered = false;

    public static function display(): bool
    {
        if (self::$rendered) {
            return true;
        }

        self::$rendered = true;

        $config = PluginFlvcCustomConfig::load();

        $backgroundStyle = self::buildBackgroundStyle($config);
        $layoutClass     = $config['layout'] ?? 'layout_a';

        include __DIR__ . '/../templates/login.php';

        return true;
    }

    private static function buildBackgroundStyle(array $config): string
    {
        switch ($config['background_mode']) {
            case 'upload':
                if (!empty($config['background_upload'])) {
                    $url = self::getPublicAssetUrl('data/uploads/' . $config['background_upload']);
                    if ($url !== '') {
                        return "background-image: url('{$url}'); background-size: cover; background-position: center;";
                    }
                }
                break;
            case 'url':
                if (!empty($config['background_url'])) {
                    $url = htmlspecialchars($config['background_url'], ENT_QUOTES, 'UTF-8');
                    return "background-image: url('{$url}'); background-size: cover; background-position: center;";
                }
                break;
            case 'color':
            default:
                $color = htmlspecialchars($config['background_color'], ENT_QUOTES, 'UTF-8');
                return "background: {$color};";
        }

        return "background: #0a3d62;";
    }

    private static function getPublicAssetUrl(string $relative): string
    {
        $root = GLPI_ROOT;
        $path = $root . '/plugins/flvc.custom/' . $relative;
        if (!file_exists($path)) {
            return '';
        }

        $baseUri = GLPI_URI;
        return $baseUri . '/plugins/flvc.custom/' . $relative;
    }
}
