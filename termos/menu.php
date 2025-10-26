<?php

function plugin_termos_menu(): array {
    return [
        'title' => __('Termos', 'termos'),
        'page'  => '/plugins/termos/front/term_form.php',
        'icon'  => 'fas fa-file-contract',
    ];
}

