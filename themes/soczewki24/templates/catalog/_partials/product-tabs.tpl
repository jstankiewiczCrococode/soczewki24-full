{**
 * Zakladki pod galeria. Opis produktu zawiera tez wymiary i blok promocyjny z makiety.
 * Zakladki dokladane przez moduly (product.extraContent) laduja za koszami wysylki.
 *}
{capture name='reviews'}{hook h='displayFooterProduct' mod='productcomments' product=$product category=$category}{/capture}

<div class="pdp-tabs">
  <div class="pdp-tabs__nav nav" role="tablist" aria-label="{l s='Informacje o produkcie' d='Shop.Theme.Catalog'}">
    <button
      class="pdp-tabs__link nav-link active"
      id="pdp-tab-description"
      type="button"
      role="tab"
      data-bs-toggle="tab"
      data-bs-target="#pdp-pane-description"
      aria-controls="pdp-pane-description"
      aria-selected="true"
    >{l s='Opis produktu' d='Shop.Theme.Catalog'}</button>

    {if $smarty.capture.reviews|trim}
      <button
        class="pdp-tabs__link nav-link"
        id="pdp-tab-reviews"
        type="button"
        role="tab"
        data-bs-toggle="tab"
        data-bs-target="#pdp-pane-reviews"
        aria-controls="pdp-pane-reviews"
        aria-selected="false"
        tabindex="-1"
      >{l s='Opinie klientów' d='Shop.Theme.Catalog'}</button>
    {/if}

    <button
      class="pdp-tabs__link nav-link"
      id="pdp-tab-shipping"
      type="button"
      role="tab"
      data-bs-toggle="tab"
      data-bs-target="#pdp-pane-shipping"
      aria-controls="pdp-pane-shipping"
      aria-selected="false"
      tabindex="-1"
    >{l s='Koszty wysyłki' d='Shop.Theme.Catalog'}</button>

    {foreach from=$product.extraContent item=extra key=extraKey}
      <button
        class="pdp-tabs__link nav-link"
        id="pdp-tab-extra-{$extraKey}"
        type="button"
        role="tab"
        data-bs-toggle="tab"
        data-bs-target="#pdp-pane-extra-{$extraKey}"
        aria-controls="pdp-pane-extra-{$extraKey}"
        aria-selected="false"
        tabindex="-1"
      >{$extra.title}</button>
    {/foreach}
  </div>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="pdp-pane-description" role="tabpanel" aria-labelledby="pdp-tab-description" tabindex="0">
      {include file='catalog/_partials/product-features.tpl' part='dimensions'}

      {hook h='displayCrocoPdpDescription' product=$product}

      {if $product.description}
        <div class="pdp-description rich-text">{$product.description nofilter}</div>
      {/if}
    </div>

    {if $smarty.capture.reviews|trim}
      <div class="tab-pane fade" id="pdp-pane-reviews" role="tabpanel" aria-labelledby="pdp-tab-reviews" tabindex="0">
        {$smarty.capture.reviews nofilter}
      </div>
    {/if}

    <div class="tab-pane fade" id="pdp-pane-shipping" role="tabpanel" aria-labelledby="pdp-tab-shipping" tabindex="0">
      <div class="pdp-shipping rich-text">
        <p>{l s='Koszt wysyłki zależy od wybranego przewoźnika i jest wyliczany w koszyku, przed złożeniem zamówienia.' d='Shop.Theme.Catalog'}</p>
        {if !empty($product.delivery_information)}
          <p>{$product.delivery_information}</p>
        {/if}
      </div>
    </div>

    {foreach from=$product.extraContent item=extra key=extraKey}
      <div class="tab-pane fade" id="pdp-pane-extra-{$extraKey}" role="tabpanel" aria-labelledby="pdp-tab-extra-{$extraKey}" tabindex="0" {foreach $extra.attr as $key => $val} {$key}="{$val}"{/foreach}>
        {$extra.content nofilter}
      </div>
    {/foreach}
  </div>
</div>
