<?php

include '../../../inc/includes.php';

Session::checkRight('config', READ);

require_once PLUGIN_TERMOS_ROOT . '/inc/termservice.class.php';

global $CFG_GLPI;

$termTypes = [
    PluginTermosTermService::TYPE_RESPONSIBILITY => __('Termo de Responsabilidade', 'termos'),
    PluginTermosTermService::TYPE_RETURN         => __('Termo de Devolução', 'termos'),
];

$supportedItemtypes = PluginTermosTermService::getSupportedItemtypes();

if (isset($_POST['generate'])) {
    $usersId  = (int)($_POST['users_id'] ?? 0);
    $itemtype = $_POST['itemtype'] ?? '';
    $itemsId  = (int)($_POST['items_id'] ?? 0);
    $termType = $_POST['term_type'] ?? PluginTermosTermService::TYPE_RESPONSIBILITY;
    $city     = trim($_POST['city'] ?? '');
    $observ   = trim($_POST['observations'] ?? '');
    $sendMail = isset($_POST['send_mail']);

    $errors = [];

    if ($usersId <= 0) {
        $errors[] = __('Selecione um usuário válido.', 'termos');
    }
    if (empty($itemtype) || !isset($supportedItemtypes[$itemtype])) {
        $errors[] = __('Selecione um tipo de ativo válido.', 'termos');
    }
    if ($itemsId <= 0) {
        $errors[] = __('Selecione um ativo válido.', 'termos');
    }

    $user = new User();
    $item = null;

    if (empty($errors) && !$user->getFromDB($usersId)) {
        $errors[] = __('Usuário não encontrado.', 'termos');
    }

    if (empty($errors)) {
        $item = getItemForItemtype($itemtype);
        if (!$item || !$item->getFromDB($itemsId)) {
            $errors[] = __('Ativo não encontrado.', 'termos');
        }
    }

    if (empty($errors)) {
        try {
            $templateData = PluginTermosTermService::buildTemplateData($user, $item, [
                'city'         => $city,
                'observations' => $observ,
                'term_label'   => $termTypes[$termType] ?? $termTypes[PluginTermosTermService::TYPE_RESPONSIBILITY],
            ]);

            $template    = PluginTermosTermService::getTemplateFilename($termType);
            $html        = PluginTermosTermService::renderHtml($template, $templateData);
            $pdfContent  = PluginTermosTermService::generatePdf($html);
            $safeUser    = preg_replace('/[^a-z0-9_-]+/i', '_', $user->getName());
            $safeAsset   = preg_replace('/[^a-z0-9_-]+/i', '_', $item->getName());
            $filename    = sprintf('%s_%s_%s.pdf', $termType, $safeUser, $safeAsset);
            $documentId  = PluginTermosTermService::createDocument($pdfContent, $filename, $item);

            if ($sendMail) {
                if (PluginTermosTermService::sendMail($user, $pdfContent, $filename, $termType)) {
                    Session::addMessageAfterRedirect(__('Termo enviado por e-mail ao usuário.', 'termos'));
                } else {
                    Session::addMessageAfterRedirect(__('Não foi possível enviar o e-mail para o usuário selecionado.', 'termos'), false, ERROR);
                }
            }

            if ($documentId) {
                global $CFG_GLPI;
                $docUrl = $CFG_GLPI['root_doc'] . '/front/document.form.php?id=' . $documentId;
                Session::addMessageAfterRedirect(sprintf(__('Termo gerado e vinculado ao ativo. Documento #%1$d: %2$s', 'termos'), $documentId, $docUrl));
            }

            $_SESSION['plugin_termos_last_pdf'] = base64_encode($pdfContent);
            $_SESSION['plugin_termos_last_filename'] = $filename;

            Html::redirect($_SERVER['REQUEST_URI']);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            Session::addMessageAfterRedirect($error, false, ERROR);
        }
        Html::redirect($_SERVER['REQUEST_URI']);
    }
}

if (isset($_GET['download']) && !empty($_SESSION['plugin_termos_last_pdf']) && !empty($_SESSION['plugin_termos_last_filename'])) {
    $content  = base64_decode($_SESSION['plugin_termos_last_pdf']);
    $filename = $_SESSION['plugin_termos_last_filename'];

    unset($_SESSION['plugin_termos_last_pdf'], $_SESSION['plugin_termos_last_filename']);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

Html::header(__('Termos', 'termos'), '', 'plugins', 'termos');

Session::displayMessages();

if (!empty($_SESSION['plugin_termos_last_pdf']) && !empty($_SESSION['plugin_termos_last_filename'])) {
    $downloadUrl = $_SERVER['PHP_SELF'] . '?download=1';
    echo '<div class="center" style="margin-bottom: 15px;">';
    echo '<a class="vsubmit" href="' . $downloadUrl . '">' . __('Baixar último PDF gerado', 'termos') . '</a>';
    echo '</div>';
}

echo "<form method='post' action='' class='center'>";

echo "<table class='tab_cadre_fixe'>";

echo '<tr class="tab_bg_1"><th>' . __('Tipo de termo', 'termos') . '</th><td>';
Dropdown::showFromArray('term_type', $termTypes, [
    'value' => PluginTermosTermService::TYPE_RESPONSIBILITY,
]);
echo '</td></tr>';

echo '<tr class="tab_bg_1"><th>' . __('Usuário', 'termos') . '</th><td>';
User::dropdown([
    'name'  => 'users_id',
    'right' => 'all',
]);
echo '</td></tr>';

echo '<tr class="tab_bg_1"><th>' . __('Tipo de ativo', 'termos') . '</th><td>';
Dropdown::showFromArray('itemtype', $supportedItemtypes, [
    'value' => array_key_first($supportedItemtypes),
    'display_emptychoice' => true,
    'emptylabel' => __('Selecione...', 'termos'),
]);
echo '</td></tr>';

echo '<tr class="tab_bg_1"><th>' . __('Ativo', 'termos') . '</th><td>';
echo "<select name='items_id' id='plugin-termos-items' required><option value=''>" . __('Selecione um ativo', 'termos') . "</option></select>";
echo '</td></tr>';

echo '<tr class="tab_bg_1"><th>' . __('Cidade', 'termos') . '</th><td><input type="text" name="city" class="form-control" /></td></tr>';

echo '<tr class="tab_bg_1"><th>' . __('Observações adicionais', 'termos') . '</th><td><textarea name="observations" rows="3" class="form-control"></textarea></td></tr>';

echo '<tr class="tab_bg_1"><th>' . __('Enviar termo por e-mail ao usuário', 'termos') . '</th><td><input type="checkbox" name="send_mail" checked></td></tr>';

echo '<tr class="tab_bg_1"><td colspan="2" class="center"><button type="submit" name="generate" class="vsubmit">' . __('Gerar termo', 'termos') . '</button></td></tr>';

echo '</table>';
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo '</form>';

$ajaxUrl = $CFG_GLPI['root_doc'] . '/plugins/termos/front/ajax/items.php';

$messages = [
    'selectType' => json_encode(__('Selecione um tipo de ativo', 'termos')),
    'loading'    => json_encode(__('Carregando...', 'termos')),
    'empty'      => json_encode(__('Nenhum ativo disponível', 'termos')),
    'error'      => json_encode(__('Erro ao carregar ativos', 'termos')),
];

$script = <<<JS
(function () {
    const itemTypeSelect = document.querySelector('select[name="itemtype"]');
    const itemSelect = document.getElementById('plugin-termos-items');
    const messages = {
        selectType: %s,
        loading: %s,
        empty: %s,
        error: %s
    };

    async function loadItems() {
        const itemtype = itemTypeSelect.value;
        if (!itemtype) {
            itemSelect.innerHTML = '<option value="">' + messages.selectType + '</option>';
            return;
        }

        itemSelect.innerHTML = '<option value="">' + messages.loading + '</option>';

        try {
            const response = await fetch(%s + '?itemtype=' + encodeURIComponent(itemtype));
            const data = await response.json();
            itemSelect.innerHTML = '';
            if (!data.length) {
                itemSelect.innerHTML = '<option value="">' + messages.empty + '</option>';
                return;
            }
            for (const item of data) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.label;
                itemSelect.appendChild(option);
            }
        } catch (error) {
            itemSelect.innerHTML = '<option value="">' + messages.error + '</option>';
        }
    }

    if (itemTypeSelect) {
        itemTypeSelect.addEventListener('change', loadItems);
        loadItems();
    }
})();
JS;

$script = sprintf(
    $script,
    $messages['selectType'],
    $messages['loading'],
    $messages['empty'],
    $messages['error'],
    json_encode($ajaxUrl)
);

echo Html::scriptBlock($script);

Html::footer();
