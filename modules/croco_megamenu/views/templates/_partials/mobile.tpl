{* order-1 na mobile: za wszystkimi elementami z order 0 (lupa, koszyk), czyli hamburger jest ostatni *}
<div class="mega-menu-mobile col-auto order-1 d-flex d-md-none align-items-center">
  <div class="header-block d-flex align-items-center">
    <button
      type="button"
      class="header-block__action-btn mega-menu-mobile__toggle border-0 bg-transparent"
      data-bs-toggle="collapse"
      data-bs-target="#mega-menu-mobile-panel"
      aria-controls="mega-menu-mobile-panel"
      aria-expanded="false"
      aria-label="{l s='Menu' d='Modules.Crocomegamenu.Shop'}"
    >
      <span class="mega-menu-mobile__icon mega-menu-mobile__icon--open">{include file='components/icon.tpl' name='list'}</span>
      <span class="mega-menu-mobile__icon mega-menu-mobile__icon--close">{include file='components/icon.tpl' name='x'}</span>
    </button>
  </div>

  <div class="collapse mega-menu-mobile__panel" id="mega-menu-mobile-panel">
    <nav aria-label="{l s='Menu glowne' d='Modules.Crocomegamenu.Shop'}">
      <ul class="list-unstyled mb-0">
        {foreach from=$megaMenuItems item=menuItem}
          <li class="mega-menu-mobile__item{if $menuItem.is_highlighted} mega-menu-mobile__item--highlight{/if}">
            {if $menuItem.has_panel}
              <button
                type="button"
                class="mega-menu-mobile__row"
                data-bs-toggle="collapse"
                data-bs-target="#mega-menu-mobile-item-{$menuItem.id_item}"
                aria-expanded="false"
                aria-controls="mega-menu-mobile-item-{$menuItem.id_item}"
              >
                <span>{$menuItem.label}</span>
                {include file='components/icon.tpl' name='caretdown'}
              </button>

              <div class="collapse" id="mega-menu-mobile-item-{$menuItem.id_item}">
                <ul class="list-unstyled mega-menu-mobile__sub">
                  {* Kolumny z desktopu splaszczone do jednej listy; baner pomijamy na mobile *}
                  {foreach from=$menuItem.columns item=column key=columnIndex}
                    {foreach from=$column.blocks item=block key=blockIndex}
                      {if $block.type === 'links'}
                        <li>
                          {if $block.title}
                            <button
                              type="button"
                              class="mega-menu-mobile__row mega-menu-mobile__row--group"
                              data-bs-toggle="collapse"
                              data-bs-target="#mega-menu-mobile-block-{$menuItem.id_item}-{$columnIndex}-{$blockIndex}"
                              aria-expanded="false"
                              aria-controls="mega-menu-mobile-block-{$menuItem.id_item}-{$columnIndex}-{$blockIndex}"
                            >
                              <span>{$block.title}</span>
                              {include file='components/icon.tpl' name='caretdown'}
                            </button>
                          {/if}
                          <ul class="list-unstyled mb-0{if $block.title} collapse{/if}" id="mega-menu-mobile-block-{$menuItem.id_item}-{$columnIndex}-{$blockIndex}">
                            {foreach from=$block.links item=link}
                              <li><a href="{$link.url}" class="mega-menu-mobile__link">{$link.label}</a></li>
                            {/foreach}
                          </ul>
                        </li>
                      {elseif $block.type === 'cta'}
                        {foreach from=$block.links item=link}
                          <li>
                            <a href="{$link.url}" class="mega-menu-mobile__link mega-menu-mobile__link--cta">
                              {$link.label}
                              {include file='components/icon.tpl' name='arrowright'}
                            </a>
                          </li>
                        {/foreach}
                      {/if}
                    {/foreach}
                  {/foreach}
                </ul>
              </div>
            {else}
              <a href="{$menuItem.url}" class="mega-menu-mobile__row">{$menuItem.label}</a>
            {/if}
          </li>
        {/foreach}
      </ul>
    </nav>

    {* Same linki bez logiki: my-account i historia zamowien wymagaja logowania, wiec gosc i tak trafia na formularz *}
    <div class="mega-menu-mobile__actions">
      <a href="{$urls.pages.my_account}" class="btn btn-primary btn-xs d-flex align-items-center justify-content-center gap-2">
        {include file='components/icon.tpl' name='user'}
        {l s='Konto użytkownika' d='Modules.Crocomegamenu.Shop'}
      </a>
      <a href="{$urls.pages.history}" class="btn btn-secondary btn-xs d-flex align-items-center justify-content-center gap-2">
        {include file='components/icon.tpl' name='arrowscounterclockwise'}
        {l s='Zamów ponownie' d='Modules.Crocomegamenu.Shop'}
      </a>
    </div>
  </div>
</div>
