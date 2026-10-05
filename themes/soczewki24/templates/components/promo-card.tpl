<aside class="promo-card{if !empty($image)} promo-card--with-image{/if}">
  <div class="promo-card__body">
    <span class="promo-card__icon promo-card__icon--{$tone|default:'primary'}" aria-hidden="true">{include file='components/icon.tpl' name=$icon}</span>
    <p class="promo-card__title">{$title}</p>
    <p class="promo-card__text">{$text}</p>
    <a href="{$link_url}" class="promo-card__link">
      {$link_label}
      {include file='components/icon.tpl' name='arrowright'}
    </a>
  </div>

  {if !empty($image)}
    <img class="promo-card__image" src="{$image}" alt="" loading="lazy">
  {/if}
</aside>
