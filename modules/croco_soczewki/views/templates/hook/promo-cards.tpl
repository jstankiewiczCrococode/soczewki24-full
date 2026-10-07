<div class="promo-cards">
  {foreach from=$promoCards item=card}
    {include file='components/promo-card.tpl'
      icon=$card.icon
      tone=$card.icon_tone
      variant=$card.variant
      title=$card.title
      text=$card.text
      link_label=$card.link_label
      link_url=$card.link_url
      image=$card.image_url
    }
  {/foreach}
</div>
