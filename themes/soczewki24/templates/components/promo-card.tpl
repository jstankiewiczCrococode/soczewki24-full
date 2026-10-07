<aside class="promo-card{if !empty($variant) && $variant != 'default'} promo-card--{$variant}{/if}{if !empty($image)} promo-card--with-image{/if}">
  <div class="promo-card__body">
    {if !empty($icon)}
      <span class="promo-card__icon promo-card__icon--{$tone|default:'primary'}" aria-hidden="true">{include file='components/icon.tpl' name=$icon}</span>
    {/if}
    <p class="promo-card__title">{$title}</p>
    {if !empty($text)}
      <p class="promo-card__text">{$text}</p>
    {/if}
    {if !empty($link_url) && !empty($link_label)}
      <a href="{$link_url}" class="promo-card__link">
        {$link_label}
        {include file='components/icon.tpl' name='arrowright'}
      </a>
    {/if}
  </div>

  {if !empty($image)}
    <img class="promo-card__image" src="{$image}" alt="" loading="lazy">
  {/if}
</aside>
