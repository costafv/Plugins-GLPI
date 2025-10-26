<?php

include '../../../../inc/includes.php';

Session::checkRight('config', READ);

header('Content-Type: application/json');

$itemtype = $_GET['itemtype'] ?? '';

if (empty($itemtype)) {
    echo json_encode([]);
    exit;
}

$item = getItemForItemtype($itemtype);
if (!$item instanceof CommonDBTM) {
    echo json_encode([]);
    exit;
}

$conditions = ['is_deleted' => 0];
if (method_exists($item, 'maybeTemplate') && $item->maybeTemplate()) {
    $conditions['is_template'] = 0;
}

if (method_exists($item, 'isEntityAssign') && $item->isEntityAssign()) {
    $conditions['entities_id'] = Session::getActiveEntities(false);
}

$items = $item->find($conditions, 'name ASC', 200);

$response = [];
foreach ($items as $data) {
    $label = $data['name'] ?? ('#' . $data['id']);
    $serialParts = array_filter([
        $data['serial'] ?? null,
        $data['otherserial'] ?? null,
        $data['asset_tag'] ?? null,
    ]);
    if (!empty($serialParts)) {
        $label .= ' (' . implode(' / ', $serialParts) . ')';
    }
    $response[] = [
        'id'    => (int) $data['id'],
        'label' => $label,
    ];
}

echo json_encode($response);
