{**
 * Galeria PDP: jedno duze zdjecie + siatka mniejszych, reszta pod "Pokaz wiecej".
 * Zdjecia otwieraja modal #product-modal (product-images-modal.tpl) na wlasciwym slajdzie.
 *}

{function name=pdpGalleryImage image=null key=0 size='small' priority=false}
  <button
    type="button"
    class="pdp-gallery__item pdp-gallery__item--{$size}"
    data-bs-toggle="modal"
    data-bs-target="#product-modal"
    data-gallery-index="{$key}"
    aria-label="{l s='Powiększ zdjęcie %number%' sprintf=['%number%' => $key + 1] d='Shop.Theme.Catalog'}"
  >
    <picture>
      {if isset($image.bySize.product_main.sources.avif)}
        <source
          srcset="
            {$image.bySize.default_xl.sources.avif} 400w,
            {$image.bySize.product_main.sources.avif} 720w,
            {$image.bySize.product_main_2x.sources.avif} 1440w"
          sizes="{if $size == 'main'}(min-width: 992px) 720px, 100vw{else}(min-width: 992px) 352px, 50vw{/if}"
          type="image/avif"
        >
      {/if}

      {if isset($image.bySize.product_main.sources.webp)}
        <source
          srcset="
            {$image.bySize.default_xl.sources.webp} 400w,
            {$image.bySize.product_main.sources.webp} 720w,
            {$image.bySize.product_main_2x.sources.webp} 1440w"
          sizes="{if $size == 'main'}(min-width: 992px) 720px, 100vw{else}(min-width: 992px) 352px, 50vw{/if}"
          type="image/webp"
        >
      {/if}

      <img
        class="pdp-gallery__image"
        srcset="
          {$image.bySize.default_xl.url} 400w,
          {$image.bySize.product_main.url} 720w,
          {$image.bySize.product_main_2x.url} 1440w"
        sizes="{if $size == 'main'}(min-width: 992px) 720px, 100vw{else}(min-width: 992px) 352px, 50vw{/if}"
        src="{$image.bySize.product_main.url}"
        width="{$image.bySize.product_main.width}"
        height="{$image.bySize.product_main.height}"
        {if $priority}fetchpriority="high"{else}loading="lazy"{/if}
        alt="{$image.legend}"
      >
    </picture>
  </button>
{/function}

{* Glowne zdjecie = okladka (zmienia sie razem z wariantem), reszta w kolejnosci z BO *}
{assign var=mainImage value=null}
{assign var=mainKey value=0}
{foreach from=$product.images item=image key=imageKey name=findMain}
  {if $smarty.foreach.findMain.first || $image.id_image == $product.default_image.id_image}
    {assign var=mainImage value=$image}
    {assign var=mainKey value=$imageKey}
  {/if}
{/foreach}

{assign var=otherImages value=[]}
{foreach from=$product.images item=image key=imageKey}
  {if $image.id_image != $mainImage.id_image}
    {$otherImages[] = ['image' => $image, 'key' => $imageKey]}
  {/if}
{/foreach}

{capture name='cover_actions'}{hook h='displayProductCoverActions' product=$product}{/capture}

<div class="pdp-gallery">
  {if !empty($smarty.capture.cover_actions)}
    <div class="pdp-gallery__actions">{$smarty.capture.cover_actions nofilter}</div>
  {/if}

  <div class="pdp-gallery__grid">
    {pdpGalleryImage image=$mainImage key=$mainKey size='main' priority=true}

    {foreach from=$otherImages item=other name=others}
      {if $smarty.foreach.others.index < 2}
        {pdpGalleryImage image=$other.image key=$other.key size='small'}
      {/if}
    {/foreach}
  </div>

  {if $otherImages|@count > 2}
    <div class="pdp-gallery__more collapse" id="pdp-gallery-more-{$product.id}">
      <div class="pdp-gallery__grid">
        {foreach from=$otherImages item=other name=rest}
          {if $smarty.foreach.rest.index >= 2}
            {pdpGalleryImage image=$other.image key=$other.key size='small'}
          {/if}
        {/foreach}
      </div>
    </div>

    <div class="pdp-gallery__toggle">
      <button
        type="button"
        class="btn btn-tertiary btn-xs pdp-gallery__toggle-button"
        data-bs-toggle="collapse"
        data-bs-target="#pdp-gallery-more-{$product.id}"
        aria-expanded="false"
        aria-controls="pdp-gallery-more-{$product.id}"
      >
        <span class="pdp-gallery__toggle-label pdp-gallery__toggle-label--more">{l s='Pokaż więcej' d='Shop.Theme.Catalog'}</span>
        <span class="pdp-gallery__toggle-label pdp-gallery__toggle-label--less">{l s='Pokaż mniej' d='Shop.Theme.Catalog'}</span>
        {include file='components/icon.tpl' name='caretdown'}
      </button>
    </div>
  {/if}
</div>
