# Soczewki24 - kontekst projektu

Sklep PrestaShop dla klienta Soczewki24, realizowany przez agencje CrocoCode.
Ten plik trzyma kontekst miedzy sesjami - stan srodowiska, podjete decyzje i pulapki,
ktore juz raz kosztowaly czas.

## Jezyk i konwencje

- Odpowiadamy i piszemy po polsku.
- W plikach i dokumentacji uzywamy mysinika "-", nigdy pauzy ani poltapuzy.
- Commity w Conventional Commits, po polsku (`feat:`, `fix:`, `chore:`).
- Branch z ID taska z ClickUpa: `feature/SOCZ-123-nazwa`.

## Stack - ustalony, nie zmieniac bez pytania

| Warstwa | Wybor |
|---|---|
| Platforma | PrestaShop 9.1.4 |
| PHP / DB | PHP 8.3, MySQL 8, Redis |
| Motyw | fork Hummingbird **v2.0.0** w `themes/soczewki24` (NIE child theme) |
| Front | Smarty w front office, Twig tylko w BO |
| Build | webpack, TypeScript, SCSS, Bootstrap 5.3, zrodla w `src/`, wynik w `assets/` |
| Lokalnie | Docker Compose |
| Deploy | GitHub Actions, build w CI, rsync po SSH |

Motyw nie ma katalogu `_dev` - komendy npm odpalamy w roocie motywu.

Repo trzyma tylko `themes/soczewki24`, `modules/croco_*`, `override`, `docker`, `bin`,
`docs`, `.github`. Core PrestaShop jest poza repo.

## Zasady pracy

- Zmiany wygladu robimy tokenami w `src/scss/abstract/variables/_tokens.scss`, nie
  edycja plikow Bootstrapa i Hummingbirda. Kazdy dotkniety plik upstreamu podnosi
  koszt nastepnego upgrade'u - lista zmian wzgledem upstreamu jest w
  `themes/soczewki24/UPGRADING.md` i ma byc aktualna.
- Szablony modulow nadpisujemy w `themes/soczewki24/modules/<nazwa>/views/templates/`,
  nigdy w samym module.
- `override/` tylko w ostatecznosci - najpierw hook, potem modul.
- Zmiany strukturalne w bazie ida do `modules/croco_soczewki/install()` albo
  `upgrade/upgrade-*.php`. To nasz odpowiednik migracji.
- Motyw musi zostac zgodny z EAA/WCAG 2.1 AA. Hummingbird 2.0 jest zgodny out of the
  box - przy zmianach w `templates/` zachowujemy strukture naglowkow, ARIA i kolejnosc
  focusu. To wymog prawny od czerwca 2025.
- Kierunek synchronizacji bazy: wylacznie prod → dev (`make db-pull`).

## Stan srodowiska lokalnego - dziala

Zweryfikowane 2026-08-13. Sklep zainstalowany, motyw aktywny, modul projektowy wgrany.

| Co | Gdzie |
|---|---|
| Sklep | http://localhost:8090 |
| Back office | http://localhost:8090/adminsoczewki (`dev@crococode.it` / `DevPass123!`) |
| Adminer | http://localhost:8081 |
| Mailpit | http://localhost:8025 |
| MySQL z hosta | port 3308 |

Stan potwierdzony: front HTTP 200 i serwuje assety motywu, BO ladowalne z assetami
Symfony, `ps_shop.theme_name = soczewki24`, `croco_soczewki` aktywny, tabela
`ps_croco_soczewki_log` utworzona przez `install()`, poczta idzie do Mailpita.

Toolchain motywu: `npm ci` czysty, stylelint 0 bledow, eslint 0 bledow, jest 38/38,
`npm run build` przechodzi (assets ~4.2 MB).

## Zmiany wprowadzone w szkielecie i ich powody

Szkielet nie startowal w dostarczonej postaci. Cztery poprawki, kazda uzgodniona:

### 1. Tag obrazu: `9.1-apache` → `9.1-8.3`

Tag `9.1-apache` istnieje, ale jest aliasem na `9.1-8.5`, czyli **PHP 8.5**, a stack
zaklada 8.3. Tag `9.1-8.3-apache` nie istnieje - PrestaShop nie publikuje kombinacji
`-<php>-apache` dla 9.1. Wariant bez sufiksu to apache, wiec poprawna forma to
`9.1-8.3`.

### 2. Montowanie modulow

Bylo `./modules:/var/www/html/modules/croco`, co ladowalo modul pod
`modules/croco/croco_soczewki` - PrestaShop czyta moduly tylko z jednego poziomu i go
nie widzial. Teraz kazdy modul montowany osobno. **Dodajac nowy modul `croco_*`
dopisz mu wlasna linie w `docker-compose.yml`.**

### 3. Wolumen `ps_core` na `/var/www/html`

Bez niego `app/config/parameters.php` i moduly wgrane przez BO zyly w warstwie zapisu
kontenera, wiec kazdy `docker compose down` albo recreate kasowal instalacje. Core
nadal pochodzi z obrazu, tyle ze jest utrwalony.

**Konsekwencja: podniesienie wersji PrestaShop = zmiana tagu obrazu ORAZ `make reset`.**
Sama podmiana tagu nic nie da, bo stary core zostanie w wolumenie.

### 4. Lata na instalator - `docker/pre-install/10-admin-dev-link.sh`

Najwazniejsza pulapka. Instalacja padala na ostatnim kroku, mimo poprawnie zalozonej
bazy.

Mechanizm: entrypoint obrazu (`docker_run.sh:59-61`) przemianowuje `/var/www/html/admin`
na `$PS_FOLDER_ADMIN` **zanim** uruchomi instalator. Tymczasem
`Install::finalize()` (`src/PrestaShopBundle/Install/Install.php:1187-1191`) nadpisuje
swoj fallback `$adminFolder = 'admin-dev'` tylko wtedy, gdy katalog `/var/www/html/admin`
nadal istnieje. Po zmianie nazwy juz nie istnieje, wiec finalize wola
`assets:install admin-dev`, katalogu nie ma i leci `PrestaShopException`. Entrypoint ma
`set -e`, wiec kontener konczy prace z kodem 1, a nieusuniety `install.lock` sprawia,
ze kolejny start wpada w galaz `exit 42`.

Wniosek: **kazde `PS_FOLDER_ADMIN` inne niz `admin` lamie automatyczna instalacje
PS 9.1 w tym obrazie.** Instalator CLI nie przyjmuje nazwy folderu admina jako
parametru, wiec nie da sie tego obejsc konfiguracja.

Lata podstawia `admin-dev` jako symlink na docelowy katalog admina, zanim ruszy
instalator. Do usuniecia, gdy upstream naprawi kolejnosc operacji.

## Pulapki, ktore juz raz ugryzly

- **`bin/console` tylko jako `www-data`.** Odpalony przez zwykle `docker exec` (czyli
  jako root) tworzy w `var/cache` katalogi nalezace do roota, przez co Apache nie moze
  pisac i **BO zwraca 500**. Uzywaj `make console CMD="..."`, `make theme-enable`,
  `make module-install`. `make cc` jest juz poprawione. Ratunek po fakcie:
  `docker exec soczewki24_ps chown -R www-data:www-data /var/www/html/var`.
- **Porty.** 8080 i 3307 zajmuje lokalnie projekt `markowe_kwiaty`, dlatego stoimy na
  8090 i 3308. `PS_DOMAIN` musi sie zgadzac z portem, bo zapisuje sie do bazy.
- **`make reset` kasuje wolumeny**, czyli baze i core. Po nim trzeba: `PS_INSTALL_AUTO`
  na `1`, `make up`, poczekac na `-- Installation successful! --`, wrocic na `0`,
  `make theme-enable`, `make module-install MOD=croco_soczewki`, ustawic SMTP na
  Mailpita (tabelka wyzej).
- **Node.** Lokalnie 22.x, `.nvmrc` i CI chca 20.19.4. Build i testy przechodza na
  obu, ale przy dziwnych bledach webpacka sprawdz wersje.
- `npm audit` zglasza 39 podatnosci w devDependencies odziedziczonych po Hummingbirdzie.
  Nie ruszamy - `audit fix --force` rozjechalby fork z upstreamem.
- Log instalatora podaje adres BO jako `/admin-dev` - to nasz symlink. Realna sciezka
  to `/adminsoczewki`.

## Czego nie ruszamy

- **Nie robimy deployu.** Hosting produkcji nie jest wybrany.
- **Nie ruszamy `.github/workflows/`.** CI i deploy czekaja na decyzje o hostingu.
- Nie wersjonujemy kodu modulow zewnetrznych - trafiaja do `docs/moduly.md` z wersja
  i zrodlem licencji.

## Otwarte decyzje

- [ ] Hosting produkcji: seohost (shared) czy VPS
- [ ] Bramka platnicza
- [ ] Kurierzy / InPost
- [ ] Fakturowanie (Fakturownia / wFirma / Comarch) lub BaseLinker jako hub
- [ ] Czy migracja ze starego sklepu (produkty, klienci, zamowienia, przekierowania SEO)
- [ ] SMTP produkcyjny - dostarczalnosc maili transakcyjnych. Do ustalenia razem
      z hostingiem, brak potwierdzenia zamowienia to telefon do obslugi klienta.
- [ ] Tokeny z Figmy - `src/scss/abstract/variables/_tokens.scss` czeka na realne wartosci

## Poczta w dev - Mailpit

Sklep wysyla maile przez **Mailpit**, czyli fałszywy serwer SMTP. Przyjmuje kazda
wiadomosc i zatrzymuje ja u siebie - nic nie wychodzi na zewnatrz. Podglad na
http://localhost:8025, API pod `/api/v1/messages`.

Chodzi o to, zeby testujac checkout na dumpie z produkcji nie wyslac potwierdzenia
zamowienia prawdziwemu klientowi i nie psuc reputacji domeny.

Konfiguracja siedzi w `ps_configuration`, czyli w bazie - **przezyje restart
kontenera, ale zginie po `make reset`**. Wtedy trzeba ja odtworzyc:

| Klucz | Wartosc |
|---|---|
| `PS_MAIL_METHOD` | `2` (1 = mail() PHP, 2 = SMTP, 3 = wylaczone) |
| `PS_MAIL_SERVER` | `mailpit` |
| `PS_MAIL_SMTP_PORT` | `1025` |
| `PS_MAIL_SMTP_ENCRYPTION` | `off` |
| `PS_MAIL_USER` / `PS_MAIL_PASSWD` | puste - Mailpit nie uwierzytelnia |

Ustawiamy przez `Configuration::updateValue()` albo w BO: Zaawansowane > E-mail.
Nie przez czysty SQL, bo PrestaShop cache'uje konfiguracje.

**Tego nie kopiujemy na produkcje** - Mailpit jest wylacznie dla dev i stagingu.
Konfiguracja nie moze tez trafic do `croco_soczewki::install()`, bo modul instaluje
sie takze na produkcji i przestawilby jej SMTP na nieistniejacy host.

Zweryfikowane realna wysylka: `Mail::Send()` z szablonem `test` dotarl do Mailpita
w calosci - szablon `modern`, HTML plus wersja tekstowa.

## Redis

Redis jest czescia docelowego stacku - **na produkcji ma obslugiwac cache i sesje**.

**W developmencie zostaje wylaczony.** Kontener `soczewki24_redis` stoi w
`docker-compose.yml` i trzyma port 6379, ale PrestaShop nie jest do niego podpiety -
lokalnie pracujemy na cache plikowym. To jest swiadoma decyzja, nie niedopatrzenie:
w dev cache Redisa maskowalby efekty zmian w szablonach i konfiguracji.

Konfiguracja Redisa pod produkcje to osobny temat do zrobienia przy wyborze hostingu.

## Dokumentacja

- `README.md` - szybki start
- `docs/architektura.md` - co gdzie siedzi, srodowiska
- `docs/moduly.md` - moduly zewnetrzne, wersje, licencje
- `themes/soczewki24/UPGRADING.md` - punkt forka i jak zaciagac zmiany z Hummingbirda
