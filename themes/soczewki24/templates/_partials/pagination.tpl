{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{$componentName = 'pagination'}

<nav class="{$componentName}__container">
  <div class="{$componentName}__number">
    {block name='pagination_summary'}
      {l s='Showing %from%-%to% of %total% item(s)' d='Shop.Theme.Catalog' sprintf=['%from%' => $pagination.items_shown_from ,'%to%' => $pagination.items_shown_to, '%total%' => $pagination.total_items]}
    {/block}
  </div>


<div class="{$componentName}__nav">
  {block name='pagination_page_list'}

    <nav aria-label="{l s='Products pagination' d='Shop.Theme.Catalog'}">

      {if $pagination.should_be_displayed}

        <ul class="{$componentName}">

          {* =====================================
             POPRZEDNIA
             ===================================== *}

          {foreach from=$pagination.pages item="page"}
            {if $page.type === 'previous'}

              <li class="page-item">
                <button
                  data-ps-data="{$page.url}"
                  class="page-link previous {['disabled' => !$page.clickable, 'js-pager-link' => $page.clickable]|classnames}"
                  {if !$page.clickable}aria-disabled="true" disabled{/if}
                  aria-label="{l s='Go to previous page' d='Shop.Theme.Actions'}"
                >
                  <img
                    src="{$urls.theme_assets}img/paggination-arrow-left.png"
                    alt="arrow"
                  >
                </button>
              </li>

            {/if}
          {/foreach}


          {* =====================================
             NUMERY STRON
             ===================================== *}

          <div class="wrapper">

            {foreach from=$pagination.pages item="page"}

              {if $page.type !== 'previous'
                && $page.type !== 'next'
                && $page.type !== 'spacer'
                && $page.page <= 5
              }

                <li class="page-item{if $page.current} active{/if}">

                  <button
                    data-ps-data="{$page.url}"
                    class="page-link {['js-pager-link' => $page.clickable]|classnames}"
                    {if !$page.clickable}aria-disabled="true"{/if}
                    {if $page.current}aria-current="page"{/if}
                    aria-label="{l s='Go to page %page%' sprintf=['%page%' => $page.page] d='Shop.Theme.Actions'}"
                  >
                    {$page.page}
                  </button>

                </li>

              {/if}

            {/foreach}


            {* =====================================
               KROPKI + OSTATNIA STRONA
               ===================================== *}

            <li class="page-item disabled">
              <span class="page-link" aria-hidden="true">&hellip;</span>
            </li>

            {foreach from=$pagination.pages item="page"}

              {if $page.type !== 'previous'
                && $page.type !== 'next'
                && $page.type !== 'spacer'
                && $page.page == 58
              }

                <li class="page-item{if $page.current} active{/if}">

                  <button
                    data-ps-data="{$page.url}"
                    class="page-link {['js-pager-link' => $page.clickable]|classnames}"
                    {if !$page.clickable}aria-disabled="true"{/if}
                    {if $page.current}aria-current="page"{/if}
                    aria-label="{l s='Go to page %page%' sprintf=['%page%' => $page.page] d='Shop.Theme.Actions'}"
                  >
                    {$page.page}
                  </button>

                </li>

              {/if}

            {/foreach}

          </div>


          {* =====================================
             NASTĘPNA
             ===================================== *}

          {foreach from=$pagination.pages item="page"}

            {if $page.type === 'next'}

              <li class="page-item">

                <button
                  data-ps-data="{$page.url}"
                  class="page-link next {['disabled' => !$page.clickable, 'js-pager-link' => $page.clickable]|classnames}"
                  {if !$page.clickable}aria-disabled="true" disabled{/if}
                  aria-label="{l s='Go to next page' d='Shop.Theme.Actions'}"
                >
                  <img
                    src="{$urls.theme_assets}img/paggination-arrow-right.png"
                    alt="arrow"
                  >
                </button>

              </li>

            {/if}

          {/foreach}

        </ul>

      {/if}

    </nav>

  {/block}
</div>


</nav>
