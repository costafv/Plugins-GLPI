<?php
/**
 * Configuration UI for flvc.custom.
 */

include '../../../inc/includes.php';
require_once __DIR__ . '/../inc/autoload.php';

Session::checkRight('config', READ);

$config = PluginFlvcCustomConfig::handleSubmission();
if ($config === null) {
    $config = PluginFlvcCustomConfig::load();
}

Html::header(__('flvc.custom - Configuração', 'flvccustom'), '', 'config', 'plugins');

$layouts = [
    'layout_a' => __('Layout A - Painel clássico', 'flvccustom'),
    'layout_b' => __('Layout B - Painel centralizado', 'flvccustom'),
    'layout_c' => __('Layout C - Painel em cards', 'flvccustom'),
];

$backgroundModes = [
    'color'  => __('Cor sólida corporativa', 'flvccustom'),
    'upload' => __('Imagem enviada', 'flvccustom'),
    'url'    => __('Imagem via URL', 'flvccustom'),
];

$uploadPreview = '';
if (!empty($config['background_upload'])) {
    $uploadPreview = GLPI_URI . '/plugins/flvc.custom/data/uploads/' . $config['background_upload'];
}
?>
<div class="flvccustom-config">
    <form method="post" enctype="multipart/form-data" class="flvccustom-form">
        <table class="tab_cadre_fixe">
            <tr class="headerRow">
                <th colspan="2"><?php echo __('Tema azul corporativo', 'flvccustom'); ?></th>
            </tr>
            <tr>
                <td><?php echo __('Layout disponível', 'flvccustom'); ?></td>
                <td>
                    <?php foreach ($layouts as $key => $label) : ?>
                        <label class="flvccustom-option">
                            <input type="radio" name="layout" value="<?php echo $key; ?>" <?php echo ($config['layout'] === $key) ? 'checked' : ''; ?>>
                            <?php echo $label; ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Modo de fundo', 'flvccustom'); ?></td>
                <td>
                    <?php foreach ($backgroundModes as $key => $label) : ?>
                        <label class="flvccustom-option">
                            <input type="radio" name="background_mode" value="<?php echo $key; ?>" <?php echo ($config['background_mode'] === $key) ? 'checked' : ''; ?>>
                            <?php echo $label; ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr class="flvccustom-color-row" <?php echo ($config['background_mode'] === 'color') ? '' : 'style="display:none;"'; ?>>
                <td><?php echo __('Cor de fundo', 'flvccustom'); ?></td>
                <td>
                    <input type="color" name="background_color" value="<?php echo htmlspecialchars($config['background_color'], ENT_QUOTES, 'UTF-8'); ?>">
                </td>
            </tr>
            <tr class="flvccustom-upload-row" <?php echo ($config['background_mode'] === 'upload') ? '' : 'style="display:none;"'; ?>>
                <td><?php echo __('Upload de imagem', 'flvccustom'); ?></td>
                <td>
                    <input type="file" name="background_upload" accept="image/*" id="flvccustom-upload">
                    <div class="flvccustom-preview">
                        <span><?php echo __('Pré-visualização', 'flvccustom'); ?></span>
                        <img id="flvccustom-upload-preview" src="<?php echo htmlspecialchars($uploadPreview, ENT_QUOTES, 'UTF-8'); ?>" alt="preview" <?php echo empty($uploadPreview) ? 'style="display:none;"' : ''; ?>>
                    </div>
                </td>
            </tr>
            <tr class="flvccustom-url-row" <?php echo ($config['background_mode'] === 'url') ? '' : 'style="display:none;"'; ?>>
                <td><?php echo __('URL de imagem', 'flvccustom'); ?></td>
                <td>
                    <input type="url" name="background_url" id="flvccustom-url" value="<?php echo htmlspecialchars($config['background_url'], ENT_QUOTES, 'UTF-8'); ?>" class="fullwidth">
                    <div class="flvccustom-preview">
                        <span><?php echo __('Pré-visualização', 'flvccustom'); ?></span>
                        <img id="flvccustom-url-preview" src="<?php echo htmlspecialchars($config['background_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="preview" <?php echo empty($config['background_url']) ? 'style="display:none;"' : ''; ?>>
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Mensagem de boas-vindas', 'flvccustom'); ?></td>
                <td>
                    <textarea name="welcome_message" rows="3" class="fullwidth"><?php echo htmlspecialchars($config['welcome_message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </td>
            </tr>
        </table>
        <div class="center">
            <input type="hidden" name="update_config" value="1">
            <button type="submit" class="btn btn-primary"><?php echo __('Salvar', 'flvccustom'); ?></button>
        </div>
    </form>
</div>
<link rel="stylesheet" type="text/css" href="<?php echo GLPI_URI; ?>/plugins/flvc.custom/css/admin.css">
<script src="<?php echo GLPI_URI; ?>/plugins/flvc.custom/js/config.js"></script>
<?php
Html::footer();
?>
