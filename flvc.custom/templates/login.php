<?php
/** @var array $config */
/** @var string $backgroundStyle */
/** @var string $layoutClass */

if (!isset($config) || !is_array($config)) {
    $config = [
        'layout'          => 'layout-a',
        'background_type' => 'color',
        'background'      => '#0b3d91',
        'welcome_message' => 'Bem-vindo ao GLPI com flvc.custom',
    ];
}

if (!isset($layoutClass) || !is_string($layoutClass)) {
    $layoutClass = $config['layout'] ?? 'layout-a';
}

if (!isset($backgroundStyle) || !is_string($backgroundStyle)) {
    $background = $config['background'] ?? '#0b3d91';
    $backgroundStyle = strpos($background, '#') === 0
        ? "background-color: {$background};"
        : "background-image: url('{$background}');";
}

if (!function_exists('__')) {
    function __(string $text): string
    {
        return $text;
    }
}

$cssPath = defined('GLPI_URI')
    ? GLPI_URI . '/plugins/flvc.custom/css/theme.css'
    : '../flvc.custom/css/theme.css';
?>
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($cssPath, ENT_QUOTES, 'UTF-8'); ?>">
<div id="flvccustom-root" class="flvccustom-wrapper" data-layout="<?php echo htmlspecialchars($layoutClass, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="flvccustom-login-card">
        <div class="flvccustom-layout <?php echo htmlspecialchars($layoutClass, ENT_QUOTES, 'UTF-8'); ?>">
            <section class="flvccustom-highlight">
                <div class="flvccustom-highlight__content">
                    <h1><?php echo htmlspecialchars($config['welcome_message'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p><?php echo __('Estamos prontos para impulsionar suas operações de TI com eficiência.', 'flvccustom'); ?></p>
                    <ul class="flvccustom-features">
                        <li>✅ <?php echo __('Tema azul corporativo inteligente', 'flvccustom'); ?></li>
                        <li>✅ <?php echo __('Layouts A, B e C configuráveis', 'flvccustom'); ?></li>
                        <li>✅ <?php echo __('Background via upload ou URL', 'flvccustom'); ?></li>
                        <li>✅ <?php echo __('Painel de configuração manual com JSON local', 'flvccustom'); ?></li>
                    </ul>
                </div>
            </section>
            <section class="flvccustom-panel">
                <div id="flvccustom-login-placeholder"></div>
            </section>
        </div>
    </div>
</div>
<script>
(function () {
    document.body.classList.add('login-page', 'flvccustom-theme');
    document.body.setAttribute('style', <?php echo json_encode($backgroundStyle, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>);

    const placeholder = document.getElementById('flvccustom-login-placeholder');
    if (!placeholder) {
        return;
    }

    const form = document.querySelector('form[name="login"]') || document.querySelector('form#login') || document.querySelector('form.login');
    if (!form) {
        return;
    }

    placeholder.appendChild(form);
    form.classList.add('flvccustom-form');
})();
</script>
