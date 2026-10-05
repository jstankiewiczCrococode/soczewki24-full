{**
 * Inne warianty (kolor/rozmiar) sprzedawane jako OSOBNE produkty, jako miniatury.
 * Kontrakt: $product_siblings = [['url' => , 'name' => , 'image' => , 'active' => bool], ...]
 * Zmienna pochodzi z modulu (faza 2) - bez danych blok sie nie renderuje.
 *}
{if !empty($product_siblings)}
  <nav class="pdp-siblings" aria-label="{l s='Inne warianty produktu' d='Shop.Theme.Catalog'}">
    <ul class="pdp-siblings__list list-unstyled">
      {foreach from=$product_siblings item=sibling}
        <li>
          <a
            class="pdp-siblings__item{if $sibling.active} pdp-siblings__item--active{/if}"
            href="{$sibling.url}"
            {if $sibling.active}aria-current="true"{/if}
          >
            <img class="pdp-siblings__image" src="{$sibling.image}" alt="{$sibling.name}" loading="lazy">
          </a>
        </li>
      {/foreach}
    </ul>
  </nav>
{/if}
