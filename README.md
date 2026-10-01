# Soczewki24 - sklep PrestaShop

Sklep na PrestaShop 9.1 z autorskim motywem (fork Hummingbird 2.0).

## Stack

| Warstwa | Wybor |
|---|---|
| Platforma | PrestaShop 9.1.4+ |
| PHP / DB | PHP 8.3, MySQL 8, Redis (cache + sesje) |
| Motyw | fork Hummingbird **v2.0.0** → `themes/soczewki24` |
| Style | SCSS + TypeScript, webpack, zrodla w `themes/soczewki24/src/` |
| Front | Smarty (front office), Twig tylko w BO |
| Lokalnie | Docker Compose |

## Szybki start

```bash
cd ~/Documents/Projects/soczewki24
cp .env.example .env          # uzupelnij dostepy do produkcji
make up                       # start kontenerow
make theme-install            # npm ci w motywie (Node z .nvmrc)
make theme-build              # produkcyjny build assetow
make theme-watch              # webpack watch
```

Motyw ma wlasny toolchain odziedziczony po Hummingbirdzie: stylelint, eslint,
jest i Storybook (`make theme-lint`, `make theme-storybook`).

- Sklep: http://localhost:8090
- Admin: http://localhost:8090/adminsoczewki (`dev@crococode.it` / `DevPass123!`)
- Adminer: http://localhost:8081
- Mailpit: http://localhost:8025 - sklep wysyla tu cala poczte, nic nie wychodzi na zewnatrz

Porty 8090 i 3308 zamiast 8080 i 3307 - te drugie zajmuje lokalnie inny projekt.

`PS_INSTALL_AUTO` jest juz ustawione na `0`, bo sklep jest zainstalowany. Na `1` wracamy
wylacznie przy instalacji od zera (`make reset`), bo instalator kasuje tabele.

Core Presty siedzi w wolumenie `ps_core`, inicjowanym z obrazu. Bez tego kazde
odtworzenie kontenera kasowaloby `app/config/parameters.php` i moduly wgrane przez BO.
Podniesienie wersji PrestaShop to zmiana tagu obrazu **oraz** `make reset`.

`bin/console` uruchamiamy zawsze jako `www-data` (`make console CMD="..."`). Odpalony
jako root zostawia w `var/cache` katalogi roota i back office przestaje dzialac.

Gdy produkcja juz stoi - zamiast czystej instalacji pobierz dane:

```bash
make db-pull
```

## Co jest w repo, a czego nie ma

**Jest:** `themes/soczewki24/` (fork), `modules/croco_*/`, `override/`, `docker/`, `bin/`, `.github/`, `docs/`.

**Nie ma:** core'a PrestaShop, modulow zewnetrznych, `vendor/`, `node_modules/`, zbudowanych assetow (`themes/soczewki24/assets/`), obrazkow produktow (`img/`), `upload/`, `download/`, `parameters.php`.

Core siedzi w obrazie Dockera lokalnie i w paczce wgranej na serwer. Podniesienie wersji Presty = podmiana obrazu i paczki, bez diffa na tysiacach plikow.

Moduly zewnetrzne (platne, bramki, kurierzy) instalujemy przez BO i zapisujemy w `docs/moduly.md` wraz z wersja i zrodlem licencji. Nie wersjonujemy ich kodu.

## Baza danych

PrestaShop trzyma polowe konfiguracji w bazie, nie w plikach. Zasada podzialu:

- **Struktura i logika** (tabele custom, rejestracja hookow, ustawienia domyslne) → `install()` i `upgrade-x.y.z.php` w naszych modulach. To jest nasz odpowiednik migracji.
- **Tresc** (produkty, kategorie, CMS, konfiguracja modulow) → **produkcja jest zrodlem prawdy**, dev pobiera zanonimizowany dump przez `make db-pull`.

Nigdy nie synchronizujemy bazy w kierunku dev → prod.

## Git

| Branch | Rola |
|---|---|
| `main` | to co na produkcji, deploy na tag `v1.2.0` |
| `develop` | auto-deploy na staging |
| `feature/SOCZ-123-nazwa` | krotkie, max 2-3 dni, PR z review |
| `fix/...` | poprawki |

Commity w Conventional Commits (`feat:`, `fix:`, `chore:`), w nazwie brancha ID taska z ClickUpa.

## Deploy

Assety buduje CI, nie serwer. Deploy przez GitHub Actions:

1. `npm ci && npm run build` w motywie
2. rsync do `releases/<sha>`
3. przepiecie symlinka `current`
4. `php bin/console cache:clear`

Katalogi wspoldzielone przez symlink miedzy releasami: `img/`, `upload/`, `download/`, `var/`, `app/config/parameters.php`.

## Konwencje kodu

- Moduly zawsze z prefiksem `croco_`
- `override/` w ostatecznosci - najpierw hooki i moduly
- Szablony modulow nadpisujemy w `themes/soczewki24/modules/<nazwa>/views/templates/`, nie w samym module
- Motyw musi zostac zgodny z EAA/WCAG - Hummingbird 2.0 jest zgodny out of the box, nie psujemy tego przy forku

## Dokumentacja

- `docs/architektura.md` - co gdzie siedzi, srodowiska, otwarte decyzje
- `docs/moduly.md` - moduly zewnetrzne, wersje i licencje
- `themes/soczewki24/UPGRADING.md` - punkt forka i jak zaciagac zmiany z Hummingbirda
