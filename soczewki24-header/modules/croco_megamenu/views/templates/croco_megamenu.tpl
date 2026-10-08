{if $megaMenuItems}
  {* order-1: zaraz za logo (order 0), przed wyszukiwarka (order-2 ms-auto) *}
  <nav class="mega-menu col-auto order-1 d-none d-md-flex align-items-center" aria-label="{l s='Menu glowne' d='Modules.Crocomegamenu.Shop'}">
    <ul class="mega-menu__list list-unstyled d-flex mb-0">
      {foreach from=$megaMenuItems item=menuItem}
        <li class="mega-menu__item{if $menuItem.is_highlighted} mega-menu__item--highlight{/if}">
          {if $menuItem.has_panel}
            <button
              type="button"
              class="mega-menu__toggle fs-body-sm fw-bold"
              aria-haspopup="true"
              aria-expanded="false"
              aria-controls="mega-menu-panel-{$menuItem.id_item}"
            >
              <span>{$menuItem.label}</span>
              <span class="mega-menu__chevron" aria-hidden="true">
                {include file='components/icon.tpl' name='caretdown'}
              </span>
            </button>
            {include file="module:croco_megamenu/views/templates/_partials/panel.tpl" menuItem=$menuItem}
          {else}
            <a href="{$menuItem.url}" class="mega-menu__link fs-body-sm fw-bold">{$menuItem.label}</a>
          {/if}
        </li>
      {/foreach}
    </ul>
  </nav>

  {include file='module:croco_megamenu/views/templates/_partials/mobile.tpl'}
{/if}
