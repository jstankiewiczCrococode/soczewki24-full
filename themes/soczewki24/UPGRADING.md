# Utrzymanie forka

Ten motyw to **fork Hummingbird v2.0.0** (https://github.com/PrestaShop/hummingbird), nie child theme. Znaczy to tyle, ze aktualizacje z upstreamu nie przyjda same - trzeba je zaciagnac recznie.

## Punkt forka

| | |
|---|---|
| Upstream | `PrestaShop/hummingbird` |
| Tag bazowy | `v2.0.0` |
| Kompatybilnosc | PrestaShop 9.1.x |
| Data forka | 2026-08-13 |

Gałąź `master` upstreamu celuje juz w PrestaShop 9.2 (Hummingbird 2.1.0). Nie zaciagaj z niej niczego, dopoki sklep nie zostanie podniesiony do 9.2.

## Jak zaciagnac zmiany z upstreamu

```bash
# jednorazowo
git remote add hummingbird https://github.com/PrestaShop/hummingbird.git
git fetch hummingbird --tags

# przy nowym wydaniu, np. v2.0.1
git diff v2.0.0 v2.0.1 -- src/ templates/ config/ > /tmp/hb.patch
# przejrzec patch, wyciac to czego nie chcemy, potem:
git apply --3way /tmp/hb.patch
```

Nie rob `git merge` z upstreamem - przy zmienionej nazwie motywu i przepisanych szablonach dostaniesz wiecej konfliktow niz pozytku. Selektywny diff jest szybszy.

## Co zmienilismy wzgledem upstreamu

Trzymaj te liste aktualna - to ona decyduje, czy nastepny upgrade zajmie godzine czy dzien.

- `config/theme.yml` - name, display_name, author, version
- `package.json` - name, version, description
- `src/scss/abstract/variables/_tokens.scss` - **nowy plik**, tokeny z Figmy
- `src/scss/abstract/variables/_index.scss` - dodany import `tokens` na poczatku
- Usuniete z forka: `.github/`, `docker/`, `CLAUDE.md`, `CONTEXT.md`, `PRODUCT.md`, `CONTRIBUTING.md`, pliki konfiguracyjne asystentow AI

## Zasady, ktore trzymaja koszt upgrade'u nisko

1. **Zmiany wygladu robimy tokenami**, nie edycja plikow Bootstrapa i Hummingbirda. Im mniej ruszonych plikow upstreamu, tym latwiejszy nastepny merge.
2. **Nowe komponenty do `src/scss/components/`**, nie doklejane do istniejacych partiali.
3. **Szablony modulow nadpisujemy w `modules/<nazwa>/views/templates/`** wewnatrz motywu - nigdy w samym module, bo update modulu to skasuje.
4. **Dostepnosc**: Hummingbird 2.0 jest zgodny z EAA/WCAG 2.1 AA out of the box. Kazda zmiana w `templates/` musi zachowac strukture naglowkow, atrybuty ARIA i kolejnosc focusu. To wymog prawny od czerwca 2025, nie nice-to-have.
