#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")"/.. && pwd)"
DEV_DIR="${ROOT_DIR}/dev"
ENV_FILE="${DEV_DIR}/.env"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "[flvc.custom] Criando arquivo .env a partir do modelo"
  cp "${DEV_DIR}/.env.example" "${ENV_FILE}"
fi

echo "[flvc.custom] Garantindo pastas de dados para Docker Compose"
mkdir -p "${DEV_DIR}/data/db" "${DEV_DIR}/data/glpi"

cat <<MSG
Ambiente preparado.
Execute:
  cd "${DEV_DIR}" && docker compose up -d
Acesse depois http://localhost:8080 para usar o GLPI com o plugin flvc.custom.
MSG
