#!/usr/bin/env bash
# =====================================================================
# Pobiera baze z produkcji, anonimizuje dane osobowe i wgrywa lokalnie.
# Kierunek TYLKO prod -> local. Nigdy odwrotnie.
#
# Wymaga pliku .env w katalogu glownym:
#   PROD_SSH_HOST=user@serwer.pl
#   PROD_DB_NAME=...
#   PROD_DB_USER=...
#   PROD_DB_PASS=...
# =====================================================================
set -euo pipefail

cd "$(dirname "$0")/.."
[ -f .env ] && set -a && source .env && set +a

DUMP="bin/prod-$(date +%Y%m%d-%H%M).sql"
LOCAL_DOMAIN="localhost:8080"

echo "==> Dump z produkcji"
ssh "$PROD_SSH_HOST" \
  "mysqldump --single-transaction --quick --no-tablespaces \
   -u'$PROD_DB_USER' -p'$PROD_DB_PASS' '$PROD_DB_NAME' \
   --ignore-table=$PROD_DB_NAME.ps_connections \
   --ignore-table=$PROD_DB_NAME.ps_connections_page \
   --ignore-table=$PROD_DB_NAME.ps_connections_source \
   --ignore-table=$PROD_DB_NAME.ps_guest \
   --ignore-table=$PROD_DB_NAME.ps_mail \
   --ignore-table=$PROD_DB_NAME.ps_log \
   --ignore-table=$PROD_DB_NAME.ps_statssearch" \
  > "$DUMP"

echo "==> Import do lokalnej bazy"
docker compose exec -T mysql mysql -uprestashop -pprestashop prestashop < "$DUMP"

echo "==> Anonimizacja"
docker compose exec -T mysql mysql -uprestashop -pprestashop prestashop <<'SQL'
-- Klienci
UPDATE ps_customer SET
  email     = CONCAT('customer', id_customer, '@example.test'),
  firstname = CONCAT('Imie', id_customer),
  lastname  = CONCAT('Nazwisko', id_customer),
  passwd    = MD5(CONCAT('devpass', id_customer)),
  birthday  = '1990-01-01',
  note      = NULL,
  ip_registration_newsletter = NULL;

-- Adresy
UPDATE ps_address SET
  firstname = CONCAT('Imie', id_address),
  lastname  = CONCAT('Nazwisko', id_address),
  address1  = CONCAT('ul. Testowa ', id_address),
  address2  = NULL,
  phone     = '000000000',
  phone_mobile = '000000000',
  other     = NULL,
  vat_number = NULL,
  dni       = NULL;

-- Zamowienia - kasujemy dane platnicze i faktury
UPDATE ps_orders SET payment = 'Test', note = NULL;
DELETE FROM ps_order_payment;
DELETE FROM ps_customer_message;
DELETE FROM ps_customer_thread;
DELETE FROM ps_newsletter;

-- Konta pracownikow BO - zostaje jedno devowe
DELETE FROM ps_employee WHERE email <> 'dev@crococode.it';

-- Przestawienie domeny
UPDATE ps_shop_url SET domain = 'localhost:8080', domain_ssl = 'localhost:8080';
UPDATE ps_configuration SET value = 'localhost:8080'
  WHERE name IN ('PS_SHOP_DOMAIN','PS_SHOP_DOMAIN_SSL');
UPDATE ps_configuration SET value = 0
  WHERE name IN ('PS_SSL_ENABLED','PS_SSL_ENABLED_EVERYWHERE','PS_SMARTY_CACHE','PS_SHOP_ENABLE');

-- Wylaczenie modulow wysylajacych na zewnatrz (uzupelnij o wasze bramki/kurierow)
UPDATE ps_module SET active = 0 WHERE name IN ('ps_emailalerts','ps_newsletter');
SQL

echo "==> Czyszczenie cache"
docker compose exec prestashop php bin/console cache:clear --no-warmup || true

rm -f "$DUMP"
echo "==> Gotowe. Dump lokalny usuniety."
