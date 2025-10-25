# Ambiente de testes do plugin flvc.custom

Este diretório fornece um ambiente local simplificado para validar o plugin flvc.custom no GLPI 11.x utilizando Docker Compose.

## Pré-requisitos
- Docker e Docker Compose Plugin instalados

## Passos para subir o ambiente
1. Entre na pasta `dev/` e gere os arquivos e diretórios necessários:
   ```bash
   cd dev
   ./bootstrap.sh
   ```
2. Com os arquivos criados, suba os serviços:
   ```bash
   docker compose up -d
   ```
3. Acesse o GLPI em [http://localhost:8080](http://localhost:8080).

O plugin `flvc.custom` é montado automaticamente no diretório de plugins do container e instalado na inicialização (via `GLPI_PLUGINS_FORCE_INSTALL`).

## Reset do ambiente
Para reiniciar o ambiente do zero, pare os serviços e apague os volumes nomeados:
```bash
docker compose down -v
rm -rf data/
```

## Simulação rápida da tela de login
Caso queira apenas visualizar o HTML/CSS do login sem subir o GLPI completo, utilize o mock disponível (a partir do diretório `dev/`):
```bash
php -S 0.0.0.0:8081 mock-login.php
```
Então abra [http://localhost:8081](http://localhost:8081).

> Observação: a simulação rápida utiliza um formulário fictício apenas para pré-visualização, sem lógica de autenticação real.
