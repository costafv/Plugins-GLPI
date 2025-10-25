#!/usr/bin/env bash
set -euo pipefail

PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OWNER="glpi"
GROUP="glpi"

if [[ $# -ge 1 ]];
then
  OWNER="$1"
fi

if [[ $# -ge 2 ]];
then
  GROUP="$2"
fi

echo "[flvc.custom] Ajustando permissões em ${PLUGIN_DIR}" >&2
chown -R "${OWNER}:${GROUP}" "${PLUGIN_DIR}"
find "${PLUGIN_DIR}" -type d -exec chmod 0750 {} +
find "${PLUGIN_DIR}" -type f -exec chmod 0640 {} +
chmod 0750 "${PLUGIN_DIR}/scripts/install.sh"

UPLOAD_DIR="${PLUGIN_DIR}/data/uploads"
if [[ ! -d "${UPLOAD_DIR}" ]]; then
  mkdir -p "${UPLOAD_DIR}"
  chmod 0750 "${UPLOAD_DIR}"
fi

echo "[flvc.custom] Permissões aplicadas (owner=${OWNER}, group=${GROUP})." >&2
