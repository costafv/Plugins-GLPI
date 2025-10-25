<?php
/**
 * Handles configuration persistence for flvc.custom.
 */

class PluginFlvcCustomConfig
{
    private const CONFIG_FILENAME = 'config.json';
    private const UPLOAD_DIR      = 'uploads';

    private static array $defaults = [
        'theme'             => 'corporate-blue',
        'layout'            => 'layout_a',
        'background_mode'   => 'color',
        'background_color'  => '#0a3d62',
        'background_url'    => '',
        'background_upload' => '',
        'welcome_message'   => 'Bem-vindo ao GLPI!'
    ];

    public static function install(): bool
    {
        $dataDir = self::getDataDir();

        if (!is_dir($dataDir) && !mkdir($dataDir, 0750, true) && !is_dir($dataDir)) {
            throw new RuntimeException('Unable to create plugin data directory.');
        }

        $configFile = self::getConfigFile();

        if (!file_exists($configFile)) {
            self::save(self::$defaults);
        }

        $uploadDir = self::getUploadDir();
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0750, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        return true;
    }

    public static function uninstall(): bool
    {
        $configFile = self::getConfigFile();
        if (file_exists($configFile)) {
            unlink($configFile);
        }

        return true;
    }

    public static function check(bool $verbose = false): bool
    {
        $configFile = self::getConfigFile();
        if (!file_exists($configFile)) {
            if ($verbose) {
                echo __('O arquivo de configuração não foi encontrado.', 'flvccustom');
            }
            return false;
        }

        return true;
    }

    public static function load(): array
    {
        $configFile = self::getConfigFile();

        if (!file_exists($configFile)) {
            return self::$defaults;
        }

        $data = json_decode((string) file_get_contents($configFile), true);

        if (!is_array($data)) {
            return self::$defaults;
        }

        return array_merge(self::$defaults, $data);
    }

    public static function save(array $data): void
    {
        $dataDir = self::getDataDir();
        if (!is_dir($dataDir) && !mkdir($dataDir, 0750, true) && !is_dir($dataDir)) {
            throw new RuntimeException('Unable to create plugin data directory.');
        }

        file_put_contents(self::getConfigFile(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function getDataDir(): string
    {
        return GLPI_ROOT . '/plugins/flvc.custom/data';
    }

    public static function getUploadDir(): string
    {
        return self::getDataDir() . '/' . self::UPLOAD_DIR;
    }

    public static function getConfigFile(): string
    {
        return self::getDataDir() . '/' . self::CONFIG_FILENAME;
    }

    public static function handleSubmission(): ?array
    {
        if (!isset($_POST['update_config'])) {
            return null;
        }

        Session::checkRight('config', UPDATE);

        $config              = self::load();
        $config['theme']     = 'corporate-blue';

        $layouts = ['layout_a', 'layout_b', 'layout_c'];
        $layout  = $_POST['layout'] ?? $config['layout'];
        $config['layout'] = in_array($layout, $layouts, true) ? $layout : 'layout_a';

        $modes   = ['color', 'upload', 'url'];
        $mode    = $_POST['background_mode'] ?? $config['background_mode'];
        $config['background_mode'] = in_array($mode, $modes, true) ? $mode : 'color';

        $config['background_color'] = $_POST['background_color'] ?? '#0a3d62';
        $config['background_url']   = $_POST['background_url'] ?? '';
        $config['welcome_message']  = $_POST['welcome_message'] ?? $config['welcome_message'];

        if (!empty($_FILES['background_upload']['name'])) {
            $upload = self::processUpload($_FILES['background_upload']);
            if ($upload !== null) {
                $config['background_upload'] = $upload;
            }
        }

        self::save($config);

        Session::addMessageAfterRedirect(__('Configuração atualizada com sucesso.', 'flvccustom'));

        return $config;
    }

    private static function processUpload(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Session::addMessageAfterRedirect(__('Falha ao enviar o arquivo de imagem.', 'flvccustom'), false, ERROR);
            return null;
        }

        $allowedMime = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
        $mime        = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowedMime, true)) {
            Session::addMessageAfterRedirect(__('Formato de imagem não suportado.', 'flvccustom'), false, ERROR);
            return null;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $basename = 'background_' . date('YmdHis') . '.' . $ext;
        $target   = self::getUploadDir() . '/' . $basename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            Session::addMessageAfterRedirect(__('Não foi possível mover o arquivo enviado.', 'flvccustom'), false, ERROR);
            return null;
        }

        return $basename;
    }
}
