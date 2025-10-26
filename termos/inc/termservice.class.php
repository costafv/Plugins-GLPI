<?php

use Dompdf\Dompdf;

class PluginTermosTermService
{
    public const TYPE_RESPONSIBILITY = 'responsibility';
    public const TYPE_RETURN = 'return';

    /**
     * List of supported GLPI itemtypes that can be associated to a term.
     *
     * @return array<string,string>
     */
    public static function getSupportedItemtypes(): array
    {
        $types = [
            Computer::class,
            Monitor::class,
            Printer::class,
            Phone::class,
            Peripheral::class,
            NetworkEquipment::class,
        ];

        $itemtypes = [];
        foreach ($types as $class) {
            if (class_exists($class)) {
                /** @var CommonDBTM $class */
                $itemtypes[$class] = $class::getTypeName(1);
            }
        }

        return $itemtypes;
    }

    public static function getTemplateFilename(string $type): string
    {
        $map = [
            self::TYPE_RESPONSIBILITY => 'termo_responsabilidade.html',
            self::TYPE_RETURN         => 'termo_devolucao.html',
        ];

        return $map[$type] ?? $map[self::TYPE_RESPONSIBILITY];
    }

    public static function buildTemplateData(User $user, CommonDBTM $item, array $options = []): array
    {
        $entityName = Dropdown::getDropdownName('glpi_entities', $item->fields['entities_id'] ?? 0);
        $locationName = Dropdown::getDropdownName('glpi_locations', $item->fields['locations_id'] ?? 0);

        $userName = formatUserName(
            $user->fields['name'] ?? '',
            $user->fields['realname'] ?? '',
            $user->fields['firstname'] ?? ''
        );

        $date = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

        if (isset($options['term_label'])) {
            $options['term_label'] = Html::entities_deep($options['term_label']);
        }

        return array_merge([
            'entity'              => Html::entities_deep($entityName),
            'location'            => Html::entities_deep($locationName),
            'item_name'           => Html::entities_deep($item->getName()),
            'item_serial'         => Html::entities_deep($item->fields['serial'] ?? ''),
            'item_otherserial'    => Html::entities_deep($item->fields['otherserial'] ?? ''),
            'item_asset_tag'      => Html::entities_deep($item->fields['asset_tag'] ?? ''),
            'item_comment'        => Html::entities_deep($item->fields['comment'] ?? ''),
            'itemtype_label'      => $item->getTypeName(1),
            'user_name'           => Html::entities_deep($userName),
            'user_login'          => Html::entities_deep($user->fields['name'] ?? ''),
            'user_registration'   => Html::entities_deep($user->fields['registrationnumber'] ?? ''),
            'user_department'     => Html::entities_deep(Dropdown::getDropdownName('glpi_groups', $user->fields['groups_id'] ?? 0)),
            'current_date'        => Html::entities_deep($date->format('d/m/Y')),
            'city'                => Html::entities_deep($options['city'] ?? ''),
            'observations'        => nl2br(Html::entities_deep($options['observations'] ?? '')),
            'responsible_name'    => Html::entities_deep(Session::getUserName()),
        ], $options);
    }

    public static function renderHtml(string $templateName, array $data): string
    {
        $templatePath = PLUGIN_TERMOS_ROOT . '/templates/' . $templateName;
        if (!is_file($templatePath)) {
            throw new RuntimeException(sprintf('Template %s não encontrado', $templateName));
        }

        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', (string) $value, $html);
        }

        return $html;
    }

    public static function generatePdf(string $html): string
    {
        $dompdf = new Dompdf([
            'isRemoteEnabled' => true,
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    public static function createDocument(string $pdfContent, string $filename, CommonDBTM $item): ?int
    {
        $directory = GLPI_DOC_DIR . '/plugins/termos';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Não foi possível criar o diretório %s', $directory));
        }

        $filepath = $directory . '/' . $filename;
        if (file_put_contents($filepath, $pdfContent) === false) {
            throw new RuntimeException(sprintf('Falha ao salvar o arquivo %s', $filepath));
        }
        $sha1 = sha1($pdfContent);

        $document = new Document();
        $docId = $document->add([
            'name'         => $filename,
            'users_id'     => Session::getLoginUserID(),
            'entities_id'  => $item->fields['entities_id'] ?? 0,
            'is_recursive' => 0,
            'mime'         => 'application/pdf',
            'filename'     => $filename,
            'filepath'     => $filepath,
            'upload_file'  => $filename,
            'sha1sum'      => $sha1,
        ]);

        if (!$docId) {
            throw new RuntimeException(__('Não foi possível criar o documento no GLPI.', 'termos'));
        }

        $docItem = new Document_Item();
        $docItem->add([
            'documents_id' => $docId,
            'itemtype'     => $item->getType(),
            'items_id'     => $item->getID(),
        ]);

        return (int) $docId;
    }

    public static function sendMail(User $user, string $pdfContent, string $filename, string $termType): bool
    {
        global $CFG_GLPI;

        $email = self::getUserEmail($user->getID());
        if (empty($email)) {
            return false;
        }

        $subject = sprintf(__('Termo de %s de ativo de TI', 'termos'), $termType === self::TYPE_RETURN ? __('devolução', 'termos') : __('responsabilidade', 'termos'));
        $body = __('Olá, segue em anexo o termo referente ao ativo de TI.', 'termos');

        $mailer = new GLPIMailer();
        if (!empty($CFG_GLPI['admin_email'])) {
            $mailer->setFrom($CFG_GLPI['admin_email']);
        }
        $mailer->addAddress($email, formatUserName($user->fields['name'] ?? '', $user->fields['realname'] ?? '', $user->fields['firstname'] ?? ''));
        $mailer->Subject = $subject;
        $mailer->isHTML(true);
        $mailer->Body = '<p>' . $body . '</p>';
        $mailer->AltBody = strip_tags($body);
        $mailer->addStringAttachment($pdfContent, $filename, 'base64', 'application/pdf');

        return $mailer->send();
    }

    public static function getUserEmail(int $usersId): ?string
    {
        global $DB;

        $query = sprintf(
            'SELECT email FROM glpi_useremails WHERE users_id = %d ORDER BY is_default DESC, id ASC LIMIT 1',
            (int) $usersId
        );

        $result = $DB->query($query);
        if ($result && $DB->numrows($result) > 0) {
            $row = $DB->fetchAssoc($result);
            return $row['email'] ?? null;
        }

        return null;
    }
}
