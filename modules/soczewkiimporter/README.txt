Soczewki24 Importer 0.3.1

Etap 3:
- importuje tylko aktualnie wyświetlany batch (20 produktów),
- nie przechodzi automatycznie do następnego batcha,
- mapuje wszystkie ścieżki g:product_type do istniejących kategorii,
- zapisuje opis, cenę, cenę promocyjną (jeśli występuje jako cena sprzedażowa), markę, EAN/GTIN, referencję MPN/feed,
  zdjęcie główne i zdjęcia dodatkowe,
- ponowny import tego samego feed ID rozpoznaje produkt po referencji FEED-<id>.

UWAGA:
Przed instalacją zachowaj kopię obecnego modułu. Wersja 0.3.1 zastępuje pliki modułu.


Wersja poprawiona: 0.3.2 — poprawiono wyszukiwanie istniejących produktów (usunięto konflikt LIMIT 1 w Db::getValue) oraz obsługę referencji FEED-{id}-... przy ponownym imporcie.
