{if $slides|count}
<section class="home-slider" data-home-slider>

  <div class="home-slider__slides">

    {foreach from=$slides item=slide name=slider}

      <div class="home-slider__slide">

        <div
          class="home-slider__image"
          {if $slide.image}
            style="background-image: url('{$image_base_url}pscustomhomeslider/{$slide.image|escape:'url'}');"
          {/if}
        ></div>

        <div class="home-slider__content">

          {if $slide.title}
            <h3>
              {$slide.title nofilter}
            </h3>
          {/if}

          {if $slide.description}
            <p>
              {$slide.description nofilter}
            </p>
          {/if}

          {if $slide.url}
            <a
              href="{$slide.url|escape:'htmlall':'UTF-8'}"
              class="home-slider__button"
            >
              {if $slide.button_text}
                <span>
                  {$slide.button_text nofilter}
                </span>
              {/if}

              <img src="" alt="" />
            </a>
          {/if}

        </div>

      </div>

    {/foreach}

  </div>

  {if $slides|count > 1}

    <div class="home-slider__dots">

      {foreach from=$slides item=slide name=dots}

        <button
          type="button"
          class="home-slider__dot"
          data-slider-dot
          aria-label="Slajd {$smarty.foreach.dots.iteration}"
        ></button>

      {/foreach}

    </div>

  {/if}

</section>
{/if}