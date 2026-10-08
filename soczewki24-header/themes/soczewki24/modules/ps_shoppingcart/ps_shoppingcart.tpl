{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div id="_desktop_ps_shoppingcart" class="order-4 col-auto px-md-0 d-none d-md-flex align-items-center">
  <div class="ps-shoppingcart">
    <div class="header-block d-flex align-items-center blockcart cart-preview {if $cart.products_count> 0}header-block--active{else}inactive{/if}" data-refresh-url="{$refresh_url}">
      {if $cart.products_count> 0}
        <a class="btn btn btn-tertiary btn-square-icon" rel="nofollow" href="{$cart_url}" aria-label="{l s='View cart (%d products)' d='Shop.Theme.Checkout' sprintf=[$cart.products_count]}">
      {else}
        <span class="btn btn-tertiary btn-square-icon">
      {/if}

      {include file='components/icon.tpl' name='shoppingbag'}

      {if $cart.products_count> 0}
        </a>
      {else}
        </span>
      {/if}
    </div>
  </div>
</div>
