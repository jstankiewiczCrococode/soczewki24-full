
{if $homeitems}
  <div class="home-items">
    <div class="container">
    {foreach from=$homeitems item=item}
      <a
        href="{$item.link|escape:'htmlall':'UTF-8'}"
        class="home-item"
        style="background-image: url('/img/homeitems/{$item.image|escape:'htmlall':'UTF-8'}');"
      >
        <div class="home-item__content">
          <h5>
            {$item.text|escape:'htmlall':'UTF-8'}
          </h5>
        </div>
      </a>
    {/foreach}
  </div>
  </div>
{/if}