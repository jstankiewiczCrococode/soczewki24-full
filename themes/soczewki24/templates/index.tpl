{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{extends file=$layout}

{block name='breadcrumb'}{/block}

{block name='content_columns'}
  {block name='left_column'}{/block}

  {block name='content_wrapper'}
    <div id="center-column" class="center-column page">
      {hook h="displayContentWrapperTop"}

      {block name='content'}
        {block name='page_content_container'}
          <div id="content" class="page-content page-content--home">
          
              {include file='components/home/slider.tpl'}
	            {hook h='displayHomeItems'}


              <section id="SectionCarusel">
                  <div class="container">
                    <div class="top">
                      <h5>Promocje</h5>
                      <a href="" >
                      <span>Sprawdź więcej<span>
                      <img src="{$urls.theme_assets}img-dist/arrow.png" alt="arrow">
                      </a>
                    </div>
                  </div>

<div class="fp-slider container">

  <div class="fp-slider__viewport">

    <div class="fp-slider__track">

      {foreach from=$listing.products item=product}

        <div class="product-item">

          <div class="fp-slider__top">

            <div class="fp-slider__try">
              <img
                src="{$urls.theme_assets}img/try.png"
                alt="Przymierz"
              >
              <span>Przymierz</span>
            </div>

            <button
              class="fp-slider__heart"
              type="button"
              aria-label="Dodaj do ulubionych"
            >
              <img
                src="{$urls.theme_assets}img/heart.png"
                alt=""
              >
            </button>

          </div>


          <a
            href="{$product.url}"
            class="fp-slider__link"
          >

            <div class="fp-slider__image">

              {if isset($product.cover.bySize.home_default.url)}

                <img
                  src="{$product.cover.bySize.home_default.url}"
                  alt="{$product.name|escape:'htmlall':'UTF-8'}"
                  loading="lazy"
                >

              {/if}

            </div>


            <div class="fp-slider__info">

              <p class="fp-slider__name">
                {$product.name|escape:'htmlall':'UTF-8'}
              </p>


              {if isset($product.reference) && $product.reference}

                <p class="fp-slider__reference">
                  {$product.reference|escape:'htmlall':'UTF-8'}
                </p>

              {/if}


              <div class="fp-slider__price">

                {if $product.has_discount}

                  <span class="fp-slider__old-price">
                    {$product.regular_price}
                  </span>

                {/if}

                <span class="fp-slider__current-price">
                  {$product.price}
                </span>

              </div>

            </div>

          </a>

        </div>

      {/foreach}

    </div>

  </div>


  <button
    class="fp-slider__arrow fp-slider__arrow--prev"
    type="button"
    aria-label="Poprzednie produkty"
  >
    &#10094;
  </button>


  <button
    class="fp-slider__arrow fp-slider__arrow--next"
    type="button"
    aria-label="Następne produkty"
  >
    &#10095;
  </button>


  <div class="fp-slider__progress">

    <div class="fp-slider__progress-bar"></div>

  </div>

</div>


<style>

/* =========================================================
   FP SLIDER
========================================================= */

.fp-slider {
  position: relative;
  width: 100%;
  padding-bottom: 35px;
}


/* =========================================================
   VIEWPORT
========================================================= */

.fp-slider__viewport {
  width: 100%;
  overflow: hidden;
}


/* =========================================================
   TRACK
========================================================= */

.fp-slider__track {
  display: flex;
  flex-wrap: nowrap;
  gap: 14px;

  width: 100%;

  overflow-x: auto;
  overflow-y: hidden;

  scroll-behavior: smooth;
  scroll-snap-type: x mandatory;

  scrollbar-width: none;
}

.fp-slider__track::-webkit-scrollbar {
  display: none;
}


/* =========================================================
   PRODUCT ITEM
========================================================= */

.fp-slider .product-item {
  position: relative;

  flex: 0 0 calc((100% - 42px) / 4);

  min-width: 0;

  background: #fff;

  scroll-snap-align: start;
}


/* =========================================================
   TOP
========================================================= */

.fp-slider__top {
  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 12px 14px 0;
}


/* =========================================================
   TRY
========================================================= */

.fp-slider__try {
  display: flex;
  align-items: center;
  gap: 5px;

  font-size: 12px;
  line-height: 1;

  color: #111;
}

.fp-slider__try img {
  width: 14px;
  height: 14px;

  object-fit: contain;
}

.fp-slider__try span {
  white-space: nowrap;
}


/* =========================================================
   HEART
========================================================= */

.fp-slider__heart {
  display: flex;
  align-items: center;
  justify-content: center;

  width: 28px;
  height: 28px;

  padding: 0;

  border: 0;

  background: transparent;

  cursor: pointer;
}

.fp-slider__heart img {
  width: 18px;
  height: 18px;

  object-fit: contain;

  transition: transform .2s ease;
}

.fp-slider__heart:hover img {
  transform: scale(1.12);
}


/* =========================================================
   LINK
========================================================= */

.fp-slider__link {
  display: block;

  color: inherit;
  text-decoration: none;
}


/* =========================================================
   IMAGE
========================================================= */

.fp-slider__image {
  width: 100%;
  height: 205px;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 15px 20px 5px;

  overflow: hidden;
}

.fp-slider__image img {
  display: block;

  width: 100%;
  height: 100%;

  object-fit: contain;

  transition: transform .3s ease;
}

.fp-slider .product-item:hover .fp-slider__image img {
  transform: scale(1.03);
}


/* =========================================================
   INFO
========================================================= */

.fp-slider__info {
  padding: 8px 14px 14px;
}


/* =========================================================
   NAME
========================================================= */

.fp-slider__name {
  margin: 0 0 7px;

  font-size: 14px;
  line-height: 1.25;
  font-weight: 500;

  color: #111;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}


/* =========================================================
   REFERENCE
========================================================= */

.fp-slider__reference {
  margin: 0 0 8px;

  font-size: 10px;
  line-height: 1.2;

  color: #999;
}


/* =========================================================
   PRICE
========================================================= */

.fp-slider__price {
  display: flex;
  align-items: center;
  gap: 7px;
}

.fp-slider__old-price {
  font-size: 11px;

  color: #888;

  text-decoration: line-through;
}

.fp-slider__current-price {
  font-size: 13px;
  font-weight: 600;

  color: #9a4d16;
}


/* =========================================================
   ARROWS
========================================================= */

.fp-slider__arrow {
  position: absolute;

  top: 50%;
  transform: translateY(-50%);

  z-index: 20;

  width: 38px;
  height: 54px;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 0;

  border: 0;

  background: rgba(255, 255, 255, .95);

  color: #111;

  font-size: 22px;

  cursor: pointer;

  box-shadow: 0 1px 5px rgba(0, 0, 0, .08);

  transition:
    background .2s ease,
    color .2s ease,
    opacity .2s ease;
}

.fp-slider__arrow:hover {
  background: #111;
  color: #fff;
}

.fp-slider__arrow:disabled {
  opacity: .25;
  cursor: default;
}

.fp-slider__arrow--prev {
  left: 0;
}

.fp-slider__arrow--next {
  right: 0;
}


/* =========================================================
   PROGRESS
========================================================= */

.fp-slider__progress {
  position: absolute;

  left: 50%;
  bottom: 5px;

  transform: translateX(-50%);

  width: 170px;
  height: 3px;

  overflow: hidden;

  background: #f1f1f1;
}

.fp-slider__progress-bar {
  width: 25%;
  height: 100%;

  background: #17202a;

  transform: translateX(0);

  transition:
    width .25s ease,
    transform .25s ease;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1100px) {

  .fp-slider .product-item {
    flex-basis: calc((100% - 28px) / 3);
  }

  .fp-slider__progress-bar {
    width: 33.333%;
  }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

  .fp-slider__track {
    gap: 10px;
  }

  .fp-slider .product-item {
    flex: 0 0 75%;
  }

  .fp-slider__image {
    height: 180px;
  }

  .fp-slider__arrow {
    width: 32px;
    height: 45px;

    font-size: 18px;
  }

  .fp-slider__progress {
    width: 130px;
  }

  .fp-slider__progress-bar {
    width: 75%;
  }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 450px) {

  .fp-slider .product-item {
    flex-basis: 82%;
  }

  .fp-slider__image {
    height: 165px;
  }

}

</style>




<script>

document.addEventListener('DOMContentLoaded', function () {

  document.querySelectorAll('.fp-slider').forEach(function (slider) {

    const track =
      slider.querySelector('.fp-slider__track');

    const prev =
      slider.querySelector('.fp-slider__arrow--prev');

    const next =
      slider.querySelector('.fp-slider__arrow--next');

    const progress =
      slider.querySelector('.fp-slider__progress-bar');


    if (!track || !prev || !next || !progress) {
      return;
    }


    function getScrollAmount() {

      const item =
        track.querySelector('.product-item');

      if (!item) {
        return 300;
      }

      const gap =
        parseFloat(
          window.getComputedStyle(track).gap
        ) || 0;

      return item.getBoundingClientRect().width + gap;

    }


    function updateSlider() {

      const maxScroll =
        track.scrollWidth - track.clientWidth;


      if (maxScroll <= 1) {

        prev.disabled = true;
        next.disabled = true;

        progress.style.width = '100%';
        progress.style.transform = 'translateX(0)';

        return;
      }


      prev.disabled =
        track.scrollLeft <= 1;

      next.disabled =
        track.scrollLeft >= maxScroll - 1;


      const visible =
        (track.clientWidth / track.scrollWidth) * 100;


      const position =
        (track.scrollLeft / maxScroll) *
        (100 - visible);


      progress.style.width =
        visible + '%';

      progress.style.transform =
        'translateX(' + position + '%)';

    }


    prev.addEventListener('click', function () {

      track.scrollBy({
        left: -getScrollAmount(),
        behavior: 'smooth'
      });

    });


    next.addEventListener('click', function () {

      track.scrollBy({
        left: getScrollAmount(),
        behavior: 'smooth'
      });

    });


    track.addEventListener(
      'scroll',
      updateSlider,
      { passive: true }
    );


    window.addEventListener(
      'resize',
      updateSlider
    );


    updateSlider();

  });

});

</script>



              </section>

          

              {hook h='displayLensFinder'}

              <section id="SectionCarusel">
                  <div class="container">
                    <div class="top">
                      <h5>Bestesellery</h5>
                      <a href="" >
                      <span>Sprawdź więcej<span>
                      <img src="{$urls.theme_assets}img-dist/arrow.png" alt="arrow">
                      </a>
                    </div>
                  </div>
<div class="fp-slider container">

  <div class="fp-slider__viewport">

    <div class="fp-slider__track">

      {foreach from=$listing.products item=product}

        <div class="product-item">

          <div class="fp-slider__top">

            <div class="fp-slider__try">
              <img
                src="{$urls.theme_assets}img/try.png"
                alt="Przymierz"
              >
              <span>Przymierz</span>
            </div>

            <button
              class="fp-slider__heart"
              type="button"
              aria-label="Dodaj do ulubionych"
            >
              <img
                src="{$urls.theme_assets}img/heart.png"
                alt=""
              >
            </button>

          </div>


          <a
            href="{$product.url}"
            class="fp-slider__link"
          >

            <div class="fp-slider__image">

              {if isset($product.cover.bySize.home_default.url)}

                <img
                  src="{$product.cover.bySize.home_default.url}"
                  alt="{$product.name|escape:'htmlall':'UTF-8'}"
                  loading="lazy"
                >

              {/if}

            </div>


            <div class="fp-slider__info">

              <p class="fp-slider__name">
                {$product.name|escape:'htmlall':'UTF-8'}
              </p>


              {if isset($product.reference) && $product.reference}

                <p class="fp-slider__reference">
                  {$product.reference|escape:'htmlall':'UTF-8'}
                </p>

              {/if}


              <div class="fp-slider__price">

                {if $product.has_discount}

                  <span class="fp-slider__old-price">
                    {$product.regular_price}
                  </span>

                {/if}

                <span class="fp-slider__current-price">
                  {$product.price}
                </span>

              </div>

            </div>

          </a>

        </div>

      {/foreach}

    </div>

  </div>


  <button
    class="fp-slider__arrow fp-slider__arrow--prev"
    type="button"
    aria-label="Poprzednie produkty"
  >
    &#10094;
  </button>


  <button
    class="fp-slider__arrow fp-slider__arrow--next"
    type="button"
    aria-label="Następne produkty"
  >
    &#10095;
  </button>


  <div class="fp-slider__progress">

    <div class="fp-slider__progress-bar"></div>

  </div>

</div>
              </section>



      
              
              <section id="product-carusel">
                <div class="container">
                  <div class="top">
                    <h5>Strefa marek</h5>
                    <a href="" >
                      <span>Sprawdź więcej<span>
                      <img src="{$urls.theme_assets}img-dist/arrow.png" alt="arrow">
                    </a>
                  </div>
                  {hook h='displayBrandSlider'}
                  </div>
              </section>

            {assign var='aboutModule' value=Module::getInstanceByName('aboutcontent')}
            {$aboutModule->renderAbout() nofilter}
          </div>
        {/block}
      {/block}

    
      {hook h='displayBlogSection'}
      
      {hook h="displayContentWrapperBottom"}
    </div>
  {/block}

{/block}

