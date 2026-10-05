{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{extends file=$layout}
{block name='breadcrumb'}{/block}

{block name='head' append}
  <meta property="og:type" content="product">
  <meta content="{$product.url}">

  {if $product.cover}
    <meta property="og:image" content="{$product.cover.large.url}">
  {/if}

  {if $product.show_price}
    <meta property="product:pretax_price:amount" content="{$product.price_tax_exc}">
    <meta property="product:pretax_price:currency" content="{$currency.iso_code}">
    <meta property="product:price:amount" content="{$product.price_amount}">
    <meta property="product:price:currency" content="{$currency.iso_code}">
  {/if}
  {if isset($product.weight) && ($product.weight != 0)}
  <meta property="product:weight:value" content="{$product.weight}">
  <meta property="product:weight:units" content="{$product.weight_unit}">
  {/if}
{/block}

{block name='head_microdata_special'}
  {include file='_partials/microdata/product-jsonld.tpl'}
{/block}

{block name='content'}
  {* Siatka: tytul, galeria, kolumna zakupowa (sticky), tresc. Klasy js-* / product-container sa wymagane przez core.js (odswiezanie wariantu przez AJAX). *}
  <div class="pdp product-container js-product-container" data-ps-ref="product-container">
    {block name='product_header'}
      <div class="pdp__title">
        <h1 class="pdp__name h5 fw-semibold">{block name='page_title'}{$product.name}{/block}</h1>
      </div>
    {/block}

    <div class="pdp__gallery">
      {block name='product_cover_thumbnails'}
        {include file='catalog/_partials/product-cover-thumbnails.tpl'}
      {/block}
    </div>

    <div class="pdp__buy" data-ps-ref="product-right" tabindex="-1">
      {block name='product_customization'}
        {if $product.is_customizable && count($product.customizations.fields)}
          {include file='catalog/_partials/product-customization.tpl' customizations=$product.customizations}
        {/if}
      {/block}

      <div class="pdp__actions js-product-actions">
        {block name='product_buy'}
          <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
            <input type="hidden" name="token" value="{$static_token}">
            <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
            <input type="hidden" name="id_customization" value="{$product.id_customization}" id="product_customization_id" class="js-product-customization-id">

            {block name='product_variants'}
              {include file='catalog/_partials/product-variants.tpl'}
            {/block}

            {block name='product_siblings'}
              {include file='catalog/_partials/product-siblings.tpl'}
            {/block}

            {block name='product_attributes'}
              {include file='catalog/_partials/product-features.tpl' part='attributes'}
            {/block}

            <hr class="pdp__divider">

            {block name='product_salon_availability'}
              {include file='components/promo-card.tpl'
                icon='mappin'
                title={l s='Zobacz, czy produkt jest dostępny w twoim salonie' d='Shop.Theme.Catalog'}
                text={l s='Zobacz czy produkt którego szukasz jest dostępny w salonie - jeśli nie, możesz go zamówić za darmo do wybranego punktu.' d='Shop.Theme.Catalog'}
                link_label={l s='Sprawdź dostępność' d='Shop.Theme.Catalog'}
                link_url=$urls.pages.stores
              }
            {/block}

            {block name='product_pack'}
              {include file='catalog/_partials/product-pack.tpl'}
            {/block}

            {block name='product_discounts'}
              {include file='catalog/_partials/product-discounts.tpl'}
            {/block}

            <div class="pdp-buybox">
              {block name='product_prices'}
                {include file='catalog/_partials/product-prices.tpl'}
              {/block}

              {block name='product_add_to_cart'}
                {include file='catalog/_partials/product-add-to-cart.tpl'}
              {/block}
            </div>

            {block name='product_additional_info'}
              {include file='catalog/_partials/product-additional-info.tpl'}
            {/block}

            {block name='product_out_of_stock'}
              {hook h='actionProductOutOfStock' product=$product}
            {/block}

            {* Input to refresh product HTML removed, block kept for compatibility with themes *}
            {block name='product_refresh'}{/block}
          </form>
        {/block}
      </div>
    </div>

    <div class="pdp__content">
      {block name='product_tabs'}
        {include file='catalog/_partials/product-tabs.tpl'}
      {/block}

      {block name='product_attachments'}
        {if $product.attachments}
          <section class="pdp-attachments">
            <h2 class="pdp-attachments__title">{l s='Download' d='Shop.Theme.Actions'}</h2>
            <ul class="list-unstyled">
              {foreach from=$product.attachments item=attachment}
                <li>
                  <a
                    href="{url entity='attachment' params=['id_attachment' => $attachment.id_attachment]}"
                    aria-label="{l s='Download %attachment_name%' sprintf=['%attachment_name%' => $attachment.name] d='Shop.Theme.Actions'}"
                  >
                    {$attachment.name} ({$attachment.file_size_formatted})
                  </a>
                </li>
              {/foreach}
            </ul>
          </section>
        {/if}
      {/block}
    </div>
  </div>

  {block name='product_accessories'}
    {if $accessories}
      {include file='catalog/_partials/product-accessories.tpl'}
    {/if}
  {/block}

  {* Opinie (productcomments) siedza w zakladce, wiec tu wszystko poza nimi *}
  {block name='product_footer'}
    {hook h='displayFooterProduct' excl='productcomments' product=$product category=$category}
  {/block}

  {block name='page_footer_container'}
    {block name='page_footer'}
    {/block}
  {/block}
{/block}
