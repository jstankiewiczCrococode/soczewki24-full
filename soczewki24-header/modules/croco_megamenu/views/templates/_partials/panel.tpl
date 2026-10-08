<div class="mega-menu__panel" id="mega-menu-panel-{$menuItem.id_item}" role="menu">
  <div class="container-md">
    <div class="mega-menu__row row gx-5">
      {foreach from=$menuItem.columns item=column}
        <div class="mega-menu__column col">
          {foreach from=$column.blocks item=block}
            {if $block.type === 'banner'}
              {foreach from=$block.links item=link}
                <a href="{$link.url}" class="mega-menu__banner">
                  {if $link.image}
                    <img src="{$link.image}" alt="" class="mega-menu__banner-img">
                  {/if}
                  <span class="mega-menu__banner-title">{$link.label}</span>
                </a>
              {/foreach}
            {elseif $block.type === 'cta'}
              {foreach from=$block.links item=link}
                <a href="{$link.url}" class="mega-menu__cta">
                  <span>{$link.label}</span>
                  <span class="mega-menu__cta-arrow" aria-hidden="true">
                    <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M9.5 1L14.5 6L9.5 11M14 6H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                </a>
              {/foreach}
            {else}
              <div class="mega-menu__block">
                {if $block.title}
                  <h3 class="mega-menu__block-title">{$block.title}</h3>
                {/if}
                <ul class="mega-menu__block-list list-unstyled">
                  {foreach from=$block.links item=link}
                    <li><a href="{$link.url}" class="mega-menu__block-link fs-body-sm fw-bold">{$link.label}</a></li>
                  {/foreach}
                </ul>
              </div>
            {/if}
          {/foreach}
        </div>
      {/foreach}
    </div>
  </div>
</div>
