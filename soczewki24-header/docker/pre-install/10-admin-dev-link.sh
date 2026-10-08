#!/bin/sh
# Lata na niezgodnosc entrypointu obrazu prestashop/prestashop z core'em PrestaShop 9.1.
#
# Problem:
#   docker_run.sh przemianowuje /var/www/html/admin na $PS_FOLDER_ADMIN ZANIM uruchomi
#   instalator. Install::finalize() (src/PrestaShopBundle/Install/Install.php) nadpisuje
#   swoj fallback $adminFolder = 'admin-dev' tylko wtedy, gdy katalog /var/www/html/admin
#   nadal istnieje. Po zmianie nazwy juz nie istnieje, wiec finalize wola
#   "assets:install admin-dev", katalogu nie ma i leci PrestaShopException.
#   Efekt: kazde PS_FOLDER_ADMIN inne niz "admin" lamie automatyczna instalacje.
#
# Rozwiazanie:
#   Podstawiamy admin-dev jako symlink na docelowy katalog admina. Instalator znajduje
#   sciezke, ktorej szuka, a assety bundli Symfony laduja tam, gdzie faktycznie stoi BO.
#
# Skrypt odpalany jest przez entrypoint PRZED zmiana nazwy katalogu admina, wiec cel
# symlinka w tym momencie jeszcze nie istnieje - to normalne, dowiaze sie chwile pozniej.
#
# Do usuniecia, gdy upstream naprawi kolejnosc operacji w docker_run.sh
# albo gdy finalize() przestanie zakladac sztywna nazwe admin-dev.

set -e

ROOT=/var/www/html
ADMIN="${PS_FOLDER_ADMIN:-admin}"

if [ "$ADMIN" = "admin" ] || [ "$ADMIN" = "admin-dev" ]; then
    echo "* [croco] PS_FOLDER_ADMIN=$ADMIN - symlink admin-dev niepotrzebny"
    exit 0
fi

echo "* [croco] Podstawiam symlink admin-dev -> $ADMIN dla instalatora PS 9.1"
ln -sfn "$ROOT/$ADMIN" "$ROOT/admin-dev"

exit 0
