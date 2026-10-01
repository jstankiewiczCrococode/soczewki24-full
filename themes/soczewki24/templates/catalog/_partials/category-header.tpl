<div id="js-product-list-header">

  {if $listing.pagination.items_shown_from == 1}

    <div class="container">
    <section class="s24-category-hero "
      {if !empty($s24_category_page.hero_background)}
        style="background-image: url('/img/{$s24_category_page.hero_background|escape:'htmlall':'UTF-8'}');"
      {/if}
    >

      <div class="s24-category-hero__content">

        {if !empty($s24_category_page.hero_title)}
          <h1 class="s24-category-hero__title">
            {$s24_category_page.hero_title|escape:'htmlall':'UTF-8'}
          </h1>
        {else}
          <h1 class="s24-category-hero__title">
            {$category.name|escape:'htmlall':'UTF-8'}
          </h1>
        {/if}

        {if !empty($s24_category_page.hero_text)}
          <p class="s24-category-hero__text">
            {$s24_category_page.hero_text|escape:'htmlall':'UTF-8'}
          </p>
        {/if}

      </div>

    </section>
    </div>



              <section id="product-carusel">
                <div class="container">
                  <div class="top">
                    <h5>
                      {$s24_category_page.brands_title|escape:'htmlall':'UTF-8'}
                   </h5>
                  </div>
                  {hook h='displayBrandSlider'}
                  </div>
              </section>

          

  

    {if !empty($s24_category_page.content_title) || !empty($s24_category_page.content_text)}
      <section class="s24-category-content container">

        {if !empty($s24_category_page.content_title)}
          <h3 class="s24-category-content__title">
            {$s24_category_page.content_title|escape:'htmlall':'UTF-8'}
          </h3>
        {/if}

      {if !empty($s24_category_page.content_text)}
        <div class="s24-category-content__text js-s24-content">
          <div class="s24-category-content__text-preview">
            {$s24_category_page.content_text nofilter}
          </div>

          <button type="button"
                  class="s24-category-content__toggle js-s24-content-toggle">
            Czytaj dalej...
          </button>
        </div>
      {/if}

      </section>
    {/if}

  {/if}

</div>
</div>
</div>

{hook h='displayBlogSection'}


<script>

document.addEventListener('click', function (e) {
  const button = e.target.closest('.js-s24-content-toggle');

  if (!button) {
    return;
  }

  const content = button.closest('.js-s24-content');

  if (!content) {
    return;
  }

  content.classList.toggle('is-expanded');

  button.textContent = content.classList.contains('is-expanded')
    ? 'Zwiń'
    : 'Czytaj dalej...';
});

</script>