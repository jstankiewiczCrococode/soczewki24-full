<section class="announcement-bar" aria-label="{l s='Komunikat' d='Modules.Crocosoczewki.Shop'}" data-announcement-bar data-announcement-id="{$bannerId}" data-announcement-cookie="{$bannerCookie}">
  <div class="container-md announcement-bar__inner">
    <p class="announcement-bar__text fw-medium">
      {$bannerText}
      {if $bannerUrl && $bannerLabel}
        <a href="{$bannerUrl}" class="announcement-bar__link fw-bold">
          {$bannerLabel}
          {include file='module:croco_soczewki/views/templates/hook/components/icon.tpl' name='arrowright'}
        </a>
      {/if}
    </p>
    <button type="button" class="announcement-bar__close btn btn-primary btn-xs btn-square-icon" data-announcement-close aria-label="{l s='Zamknij komunikat' d='Modules.Crocosoczewki.Shop'}">
      {include file='module:croco_soczewki/views/templates/hook/components/icon.tpl' name='x'}
    </button>
  </div>
</section>
