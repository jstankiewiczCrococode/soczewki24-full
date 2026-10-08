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
- `src/scss/prestashop/layout/_header-bottom.scss` - `position: relative` na `.header-bottom`, jako kontekst pozycjonowania mega-panelu na cala szerokosc
- `src/scss/prestashop/modules/_index.scss` - dodany import `megamenu`
- `src/scss/prestashop/layout/_index.scss` - dodany import `header-banner`, usuniety import `header-top`
- `src/scss/prestashop/layout/_header-top.scss` - **usuniety**, pasek `header-top` nie istnieje w naszym designie
- `templates/_partials/header.tpl` - usuniety blok `header_nav` (pasek `header-top` z hookami `displayNav1` i `displayNav2`). Hooki `displayNav1`/`displayNav2` nie sa juz nigdzie wywolywane. Usuniety takze mobilny placeholder `_mobile_ps_customersignin` (ikona konta w mobilnym wierszu), bo konto jest przyciskiem w menu mobilnym `croco_megamenu`
- `config/theme.yml` - usuniete wpisy `displayNav1` i `displayNav2`, `ps_shoppingcart` i `ps_customersignin` przeniesione do `displayTop`, `ps_mainmenu` usuniety z `modules_to_hook` i dodany do `modules_to_unhook` (`displayTop`), bo menu robi `croco_megamenu`. Uwaga: `modules_to_hook` PRZENOSI wymienione moduly (zdejmuje je ze wszystkich innych hookow), a modul niewymieniony zachowuje hooki z wlasnego `install()`, dlatego samo wyciecie z `modules_to_hook` nie wystarczy - trzeba jawnie odpiac
- `src/scss/prestashop/layout/_header-banner.scss` - **nowy plik**, pasek komunikatu nad headerem (hook `displayBanner`, tresc w `croco_soczewki`)
- `src/js/theme.ts` - import i wywolanie `initAnnouncementBar()` obok `initSearchbar()`
- `src/js/constants/selectors-map.ts` - dodany obiekt `announcementBar` i wpis w `selectorsMap`
- `src/js/announcement-bar.ts` - **nowy plik**, zamykanie paska i zapis ciasteczka
- `src/scss/prestashop/modules/_megamenu.scss` - **nowy plik**, style modulu `croco_megamenu`
- `src/scss/prestashop/modules/_searchbar.scss` - reguly widgetu w offcanvasie obowiazuja na kazdym breakpoincie (dawny blok `media-breakpoint-down(md)` usuniety)
- `modules/ps_searchbar/ps_searchbar.tpl` - **przepisany**: wersja desktop z inputem w headerze usunieta, sama lupa otwierajaca offcanvas na kazdym breakpoincie, widget na stale w offcanvasie. Zmiany z upstreamu w tym szablonie trzeba przenosic recznie, `git apply` nie zadziala
- `templates/components/icon.tpl` + `templates/components/icons/*.svg` - **nowe pliki**, ikony SVG inline z Figmy (`currentColor`)
- `src/scss/prestashop/components/_icon.scss` - **nowy plik**, rozmiar ikon; import dopisany na koncu `components/_index.scss`
- `modules/ps_shoppingcart/ps_shoppingcart.tpl` - klasy ukladu na wrapperze (`order-4 col-auto px-md-0 d-none d-md-flex align-items-center`)
- `modules/ps_customersignin/ps_customersignin.tpl` - klasy ukladu na wrapperze (`order-5 col-auto px-md-0 d-none d-md-flex align-items-center`)
- `templates/catalog/product.tpl` - **przepisany**: siatka `.pdp` (galeria, tytul, kolumna zakupowa, tresc - kolejnosc DOM = kolejnosc na mobile; na mobile cechy i karta promo schodza pod buybox przez `order` w `_product-page.scss`) zamiast `.product__container`. Usuniete wzgledem upstreamu, bo nie ma ich w makiecie: producent pod tytulem, krotki opis, akordeon (opis, szczegoly, zalaczniki, `extraContent` - teraz zakladki), `displayReassurance`; `product-details.tpl` nie jest includowany. Breadcrumb renderowany w `.pdp__title` nad `h1` wlasnym markupem (nie `_partials/breadcrumb.tpl`, bo pusty blok `breadcrumb` w tym szablonie nadpisuje blok w partialu). Klasy `js-*`, `product-container` i `data-ps-ref` zostaja, bo wymaga ich `core.js` (odswiezanie wariantu AJAX) i `productcomments.ts`. Karty promocyjne (kolumna zakupowa i zakladka opisu) nie sa wpisane w szablon, tylko wolane hookami `displayCrocoPdpSidebar` i `displayCrocoPdpDescription` z modulu `croco_soczewki` (tresc, miejsce i kategorie zarzadzane w BO: Wyglad > Karty promocyjne)
- `templates/catalog/_partials/product-cover-thumbnails.tpl` - w quickview zostaje karuzela, na stronie produktu galeria `product-gallery-grid.tpl`. Rozpoznanie: parametr `quickview` (pierwsze otwarcie) albo `quickview=1` w zadaniu (odswiezenie wariantu)
- `templates/catalog/_partials/quickview.tpl` - przekazuje `quickview=true` do galerii
- `templates/catalog/_partials/product-prices.tpl` - slot na najnizsza cene z 30 dni (`displayProductPriceBlock`, `type='lowest_price'`)
- `config/theme.yml` - custom hook `displayProductCoverActions` (akcje nad glownym zdjeciem)
- `templates/catalog/_partials/product-gallery-grid.tpl`, `product-features.tpl`, `product-siblings.tpl`, `product-tabs.tpl` oraz `templates/components/promo-card.tpl` - **nowe pliki** strony produktu
- `src/scss/prestashop/pages/_product-page.scss` + `components/_promo-card.scss` - **nowe pliki**; importy dopisane w `pages/_index.scss` i `components/_index.scss`
- `src/scss/prestashop/components/_icon.scss` - normalizacja koloru obejmuje tez `stroke` (ikony liniowe)
- `src/js/product-gallery.ts` + `product-gallery.test.ts` - **nowe pliki**, otwieranie modala na klikniatym zdjeciu; wywolanie w `theme.ts`
- Usuniete z forka: `.github/`, `docker/`, `CLAUDE.md`, `CONTEXT.md`, `PRODUCT.md`, `CONTRIBUTING.md`, pliki konfiguracyjne asystentow AI

## Zasady, ktore trzymaja koszt upgrade'u nisko

1. **Zmiany wygladu robimy tokenami**, nie edycja plikow Bootstrapa i Hummingbirda. Im mniej ruszonych plikow upstreamu, tym latwiejszy nastepny merge.
2. **Nowe komponenty do `src/scss/components/`**, nie doklejane do istniejacych partiali.
3. **Szablony modulow nadpisujemy w `modules/<nazwa>/views/templates/`** wewnatrz motywu - nigdy w samym module, bo update modulu to skasuje.
4. **Dostepnosc**: Hummingbird 2.0 jest zgodny z EAA/WCAG 2.1 AA out of the box. Kazda zmiana w `templates/` musi zachowac strukture naglowkow, atrybuty ARIA i kolejnosc focusu. To wymog prawny od czerwca 2025, nie nice-to-have.
