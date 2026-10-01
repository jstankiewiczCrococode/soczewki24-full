# Architektura - Soczewki24

## Co gdzie siedzi

```
PrestaShop 9.1 (core - poza repo, w obrazie Dockera / paczce na serwerze)
├── themes/soczewki24/     ← REPO. Fork Hummingbird 2.0.0, SCSS + TS, webpack
├── modules/croco_soczewki/← REPO. Logika projektowa, migracje DB
├── modules/<zewnetrzne>/  ← poza repo, instalowane przez BO, spisane w moduly.md
├── override/              ← REPO, ale uzywamy w ostatecznosci
├── img/ upload/ download/ ← poza repo, symlinki wspoldzielone miedzy release'ami
└── var/                   ← poza repo, cache i logi
```

## Warstwa prezentacji

Front office to **Smarty** (`templates/*.tpl`), nie Twig. Twig jest tylko w back office.

Style: `src/scss` → webpack → `assets/css`. Skrypty: `src/js` (TypeScript) → `assets/js`.
Buduje CI, nie serwer, wiec `assets/` jest w `.gitignore`.

Tokeny z Figmy siedza w `src/scss/abstract/variables/_tokens.scss` i mapuja sie na
zmienne Bootstrapa 5.3. Zmiana palety = edycja jednego pliku, nie 40 komponentow.

## Warstwa danych

PrestaShop trzyma polowe konfiguracji w bazie. Podzial odpowiedzialnosci:

| Co | Gdzie zrodlo prawdy |
|---|---|
| Struktura tabel, hooki, ustawienia domyslne | `croco_soczewki/install()` + `upgrade/upgrade-*.php` |
| Produkty, kategorie, CMS, konfiguracja modulow | Produkcja, pobierane przez `make db-pull` |

Kierunek synchronizacji bazy: **wylacznie prod → dev**.

## Srodowiska

| | URL | Baza | Deploy |
|---|---|---|---|
| local | localhost:8090 | Docker, anonimizowany dump | `make up` |
| staging | TBD | wlasna | push na `develop` |
| prod | soczewki24.pl | wlasna | tag `v*` |

## Otwarte decyzje

- [ ] Hosting produkcji: seohost (shared) czy VPS
- [ ] Bramka platnicza
- [ ] Kurierzy / InPost
- [ ] Fakturowanie (Fakturownia / wFirma / Comarch) lub BaseLinker jako hub
- [ ] Czy migracja ze starego sklepu (produkty, klienci, zamowienia, przekierowania SEO)
