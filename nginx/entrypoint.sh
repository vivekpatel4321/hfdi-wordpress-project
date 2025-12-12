#!/bin/sh
set -e

CERT_DIR="/etc/nginx/certs"
CERT="$CERT_DIR/selfsigned.crt"
KEY="$CERT_DIR/selfsigned.key"

mkdir -p "$CERT_DIR"

if [ ! -f "$CERT" ] || [ ! -f "$KEY" ]; then
  echo "[Entrypoint] Generating self-signed SSL certificate..."
  openssl req -x509 -nodes -days 3065 -newkey rsa:2048 \
    -keyout "$KEY" \
    -out "$CERT" \
    -subj "/C=KE/ST=Nairobi/L=Nairobi/O=HFGroup/OU=IT/CN=localhost"
else
  echo "[Entrypoint] SSL certificate already exists."
fi

exec "$@"
