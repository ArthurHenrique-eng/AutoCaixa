#!/usr/bin/env bash
# Backup do MySQL com retenção. Os arquivos ficam FORA do diretório público, com permissão restrita.
# Uso: DB_PASS=... BACKUP_DIR=/home/usuario/backups/autocaixa bash scripts/backup.sh
set -euo pipefail
: "${DB_NAME:=autocaixa}"
: "${DB_USER:=autocaixa}"
: "${DB_HOST:=127.0.0.1}"
: "${DB_PASS:?Defina DB_PASS}"
: "${BACKUP_DIR:?Defina BACKUP_DIR (fora de public/)}"
: "${RETENTION_DAYS:=14}"

case "$BACKUP_DIR" in
  *public*) echo "BACKUP_DIR não pode estar dentro de public/."; exit 1 ;;
esac

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"
stamp=$(date +%Y%m%d-%H%M%S)
out="$BACKUP_DIR/autocaixa-$stamp.sql.gz"

MYSQL_PWD="$DB_PASS" mysqldump -h "$DB_HOST" -u "$DB_USER" \
  --single-transaction --routines --triggers "$DB_NAME" | gzip > "$out"
chmod 600 "$out"

find "$BACKUP_DIR" -name 'autocaixa-*.sql.gz' -mtime +"$RETENTION_DAYS" -delete
echo "Backup gerado: $out"
