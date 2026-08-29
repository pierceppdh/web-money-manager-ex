#!/bin/sh
set -e

DATA_DIR="${MMEX_DATA_DIR:-/data}"
DB_PATH="${MMEX_DB_PATH:-$DATA_DIR/MMEX_New_Transaction.db}"
CONFIG_PATH="${MMEX_CONFIG_PATH:-$DATA_DIR/configuration_user.php}"
ATTACH_DIR="${MMEX_ATTACHMENTS_DIR:-$DATA_DIR/attachments}"

mkdir -p "$(dirname "$DB_PATH")" "$(dirname "$CONFIG_PATH")" "$ATTACH_DIR"

# Homelab bind mounts are often owned by the OMV user; Apache must write the DB.
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data "$DATA_DIR" 2>/dev/null || true
    chmod -R u+rwX "$DATA_DIR" 2>/dev/null || true
fi

exec "$@"
