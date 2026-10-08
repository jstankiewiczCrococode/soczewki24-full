<div id="js-product-list-header">



  {* =========================================
     ELEMENTY TYLKO NA PIERWSZEJ STRONIE
     ========================================= *}

  {if $listing.pagination.items_shown_from == 1}

    <div class="container">
      <section class="s24-category-hero"
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

  {/if}


  {* =========================================
     PRODUKTY - NA KAŻDEJ STRONIE
     ========================================= *}

  <section id="products-list" class="container">

    <div class="top">

      <div class="filters">
        <img src="{$urls.theme_assets}img/filters-btn.png" alt="filters">
        <p>Wszystkie filtry</p>
      </div>


      <div class="sort desctop-sort">

        <h6>Sortuj wg:</h6>

        <div class="sort__select">

          <select name="sort" aria-label="Sortuj produkty">

            <option value="default" selected>
              Domyślnie
            </option>

            <option value="price-asc">
              Nazwa, rosnąco
            </option>

            <option value="price-desc">
              Cena, rosnąco
            </option>

            <option value="name-asc">
              Nazwa, malejąco
            </option>

            <option value="name-desc">
              Cena, malejąco
            </option>

          </select>

        </div>

      </div>

    </div>


    <div class="items-number"> 
      <p>{$listing.pagination.total_items} Wyniki</p> 

      <div class="sort mobile-sort">

        <h6>Sortuj wg:</h6>

        <div class="sort__select">

          <select name="sort" aria-label="Sortuj produkty">

            <option value="default" selected>
              Domyślnie
            </option>

            <option value="price-asc">
              Nazwa, rosnąco
            </option>

            <option value="price-desc">
              Cena, rosnąco
            </option>

            <option value="name-asc">
              Nazwa, malejąco
            </option>

            <option value="name-desc">
              Cena, malejąco
            </option>

          </select>

        </div>

      </div>
    </div>


    <div class="products">

      {foreach from=$listing.products item=product}

        <div class="product-item">

          <div class="top-col">

            <div class="col">
              <img
                src="{$urls.theme_assets}img/try.png"
                alt="try"
              >

              <p>Przymierz</p>
            </div>


            <div id="heart">
              <img
                src="{$urls.theme_assets}img/heart.png"
                alt="heart"
              >
            </div>

          </div>


          <a href="{$product.url}" class="product-link">


          <div class="photo-wrapper">
            <img
              class="product-photo"
              src="{$product.cover.bySize.home_default.url}"
              alt="{$product.name|escape:'htmlall':'UTF-8'}"
            >
          </div>


            <div class="bottom">

              <p class="product-name">
                {$product.name|escape:'htmlall':'UTF-8'}
              </p>


              {if isset($product.reference) && $product.reference}

                <p class="product-details">
                  {$product.reference|escape:'htmlall':'UTF-8'}
                </p>

              {/if}


              <div class="poduct-price">

                {if $product.has_discount}

                  <p class="old">
                    {$product.regular_price}
                  </p>

                {/if}


                <p class="curet">
                  {$product.price}
                </p>

              </div>

            </div>

          </a>

        </div>

      {/foreach}

    </div>


    {* =========================================
       PAGINACJA - NA KAŻDEJ STRONIE
       ========================================= *}

    {include file='_partials/pagination.tpl' pagination=$listing.pagination}

  </section>



{* =========================================
   FILTRY - NA KAŻDEJ STRONIE
   ========================================= *}

<section id="filters-list" class="">

  <div class="content">


    <div class="top">

      <h4>Filtry</h4>

      <div id="close">
        <img
          src="{$urls.theme_assets}img/x.png"
          alt="x"
        >
      </div>

    </div>


    <div class="active-filters" id="active-filters">
    </div>


    {* =========================================
       STYL
       ========================================= *}

    <div class="items">

      <div class="nav">

        <h6>Styl</h6>

        <img
          src="{$urls.theme_assets}img/arrow.png"
          alt="arrow"
        >

      </div>


      <div class="item-content">

        <div class="checbox-list">

          {foreach from=$modules.s24categorypage.styl_values item=styl}

            <label class="check-item">

              <input
                type="checkbox"
                name="styl[]"
                value="{$styl.id_feature_value}"
                data-filter-value="{$styl.value|escape:'htmlall':'UTF-8'}"
              >

              <span class="checkbox__box"></span>

              <span>{$styl.value|escape:'htmlall':'UTF-8'}</span>

            </label>

          {/foreach}

        </div>

      </div>

    </div>


    {* =========================================
       KSZTAŁT
       ========================================= *}

    <div class="items">

      <div class="nav">

        <h6>Kształt</h6>

        <img
          src="{$urls.theme_assets}img/arrow.png"
          alt="arrow"
        >

      </div>


      <div class="item-content">

        <div class="checbox-list">

          {foreach from=$modules.s24categorypage.ksztalt_values item=ksztalt}

            <label class="check-item">

              <input
                type="checkbox"
                name="ksztalt[]"
                value="{$ksztalt.id_feature_value}"
                data-filter-value="{$ksztalt.value|escape:'htmlall':'UTF-8'}"
              >

              <span class="checkbox__box"></span>

              <span>
                {$ksztalt.value|escape:'htmlall':'UTF-8'}
              </span>

            </label>

          {/foreach}

        </div>

      </div>

    </div>


    {* =========================================
       MATERIAŁ
       ========================================= *}

    <div class="items">

      <div class="nav">

        <h6>Materiał</h6>

        <img
          src="{$urls.theme_assets}img/arrow.png"
          alt="arrow"
        >

      </div>


      <div class="item-content">

        <div class="checbox-list">

          {foreach from=$modules.s24categorypage.material_values item=material}

            <label class="check-item">

              <input
                type="checkbox"
                name="material[]"
                value="{$material.id_feature_value}"
                data-filter-value="{$material.value|escape:'htmlall':'UTF-8'}"
              >

              <span class="checkbox__box"></span>

              <span>
                {$material.value|escape:'htmlall':'UTF-8'}
              </span>

            </label>

          {/foreach}

        </div>

      </div>

    </div>


    {* =========================================
       ROZMIAR
       ========================================= *}

    <div class="items">

      <div class="nav">

        <h6>Rozmiar</h6>

        <img
          src="{$urls.theme_assets}img/arrow.png"
          alt="arrow"
        >

      </div>


      <div class="item-content">

        <div class="checbox-list">

          {foreach from=$modules.s24categorypage.rozmiar_values item=rozmiar}

            <label class="check-item">

              <input
                type="checkbox"
                name="rozmiar[]"
                value="{$rozmiar.id_feature_value}"
                data-filter-value="{$rozmiar.value|escape:'htmlall':'UTF-8'}"
              >

              <span class="checkbox__box"></span>

              <span>
                {$rozmiar.value|escape:'htmlall':'UTF-8'}
              </span>

            </label>

          {/foreach}

        </div>

      </div>

    </div>


    {* =========================================
       CENA
       ========================================= *}

    <div class="items">

      <div class="nav">

        <h6>Cena</h6>

        <img
          src="{$urls.theme_assets}img/arrow.png"
          alt="arrow"
        >

      </div>


      <div class="item-content">

        <div class="price-slider">

          <div class="price-slider__track"></div>

          <div class="price-slider__range"></div>

            <input 
              class="price-slider__input price-slider__input--min" 
              type="range" 
              min="0" 
              max="{$modules.s24categorypage.max_product_price|intval}"
              value="0" 
            >


        <input 
          class="price-slider__input price-slider__input--max" 
          type="range" 
          min="0" 
          max="{$modules.s24categorypage.max_product_price|intval}"
          value="{$modules.s24categorypage.max_product_price|intval}"
        >

          <div class="price-slider__labels">

            <span class="price-slider__min-value">
              0 zł
            </span>

            <span class="price-slider__max-value">
              {$modules.s24categorypage.max_product_price|string_format:"%.0f"} zł
            </span>

          </div>

        </div>


      </div>

    </div>


    {* =========================================
       KOLOR
       ========================================= *}

    <div class="items">

      <div class="nav">

        <h6>Kolor</h6>

        <img
          src="{$urls.theme_assets}img/arrow.png"
          alt="arrow"
        >

      </div>


      <div class="item-content">

        <div class="color-items">

          {if $modules.s24categorypage.kolor_values|@count > 0}

            {foreach from=$modules.s24categorypage.kolor_values item=color}

              <label
                class="color-item"
              >

                <input
                  type="checkbox"
                  name="kolor[]"
                  value="{$color.id_feature_value}"
                  data-filter-value="{$color.value|escape:'htmlall':'UTF-8'}"
                >

                <div
                  class="color-box"
                  {if $color.image}
                    style="background-image: url('/img/{$color.image|escape:'htmlall':'UTF-8'}');"
                  {/if}
                ></div>

                <span>
                  {$color.value|escape:'htmlall':'UTF-8'}
                </span>

              </label>

            {/foreach}

          {/if}

        </div>

      </div>

    </div>

    <div

  <div class="filters-actions"> 
      <button type="button" id="resset" class="btn btn-secondary">Resetuj</button>
      <button type="button" id="apply-filters" class="btn btn-primary"> Filtruj </button>
  
  </div>

  </div>

</section>






  <div class="bg"></div>




  {if $listing.pagination.items_shown_from == 1}

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


            <button
              type="button"
              class="s24-category-content__toggle js-s24-content-toggle"
            >
              Czytaj dalej...
            </button>

          </div>

        {/if}

      </section>

    {/if}

  {/if}

</div>


{hook h='displayBlogSection'}


<script>

/* =========================================
   CZYTAJ DALEJ / ZWIŃ
   ========================================= */

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

  button.textContent =
    content.classList.contains('is-expanded')
      ? 'Zwiń'
      : 'Czytaj dalej...';

});


/* =========================================
   FILTRY
   ========================================= */

document.addEventListener("DOMContentLoaded", () => {

  const filtersBtn = document.querySelector(".filters");

  const filtersList = document.querySelector("#filters-list");

  const bg = document.querySelector(".bg");

  const closeBtn = document.querySelector("#close");


  if (!filtersBtn || !filtersList || !bg) {
    return;
  }


  const openFilters = () => {

    filtersList.classList.add("filters-open");

    bg.classList.add("display");

    document.documentElement.classList.add("no-scroll");

    document.body.classList.add("no-scroll");

  };


  const closeFilters = () => {

    filtersList.classList.remove("filters-open");

    bg.classList.remove("display");

    document.documentElement.classList.remove("no-scroll");

    document.body.classList.remove("no-scroll");

  };


  // OTWIERANIE FILTRÓW

  filtersBtn.addEventListener("click", () => {

    openFilters();

  });


  // ZAMKNIĘCIE PRZEZ TŁO

  bg.addEventListener("click", () => {

    closeFilters();

  });


  // ZAMKNIĘCIE PRZEZ X

  if (closeBtn) {

    closeBtn.addEventListener("click", () => {

      closeFilters();

    });

  }


  // ROZWIJANIE POSZCZEGÓLNYCH FILTRÓW

  document
    .querySelectorAll("#filters-list .items .nav")
    .forEach((nav) => {

      nav.addEventListener("click", () => {

        const item = nav.closest(".items");

        item.classList.toggle("open");

      });

    });

});


/* =========================================
   SLIDER CENY
   ========================================= */

document.querySelectorAll('.price-slider').forEach((slider) => {

  const minInput =
    slider.querySelector('.price-slider__input--min');

  const maxInput =
    slider.querySelector('.price-slider__input--max');


  const range =
    slider.querySelector('.price-slider__range');


  const minLabel =
    slider.querySelector('.price-slider__min-value');

  const maxLabel =
    slider.querySelector('.price-slider__max-value');


  const min = Number(minInput.min);

  const max = Number(minInput.max);


  const formatPrice = (value) => {

    return value + ' zł';

  };


  const updateSlider = () => {

    let minValue = Number(minInput.value);

    let maxValue = Number(maxInput.value);


    if (minValue > maxValue) {

      minValue = maxValue;

      minInput.value = minValue;

    }


    const minPercent =
      ((minValue - min) / (max - min)) * 100;


    const maxPercent =
      ((maxValue - min) / (max - min)) * 100;


    range.style.left =
      minPercent + '%';


    range.style.right =
      (100 - maxPercent) + '%';


    minLabel.textContent =
      formatPrice(minValue);


    maxLabel.textContent =
      formatPrice(maxValue);

  };


  minInput.addEventListener('input', () => {

    if (
      Number(minInput.value) >
      Number(maxInput.value)
    ) {

      minInput.value =
        maxInput.value;

    }

    updateSlider();

  });


  maxInput.addEventListener('input', () => {

    if (
      Number(maxInput.value) <
      Number(minInput.value)
    ) {

      maxInput.value =
        minInput.value;

    }

    updateSlider();

  });


  updateSlider();

});


/* =========================================
   PAGINACJA
   ========================================= */

document.addEventListener('click', function (e) {

  const link =
    e.target.closest('.js-pager-link');


  if (!link) {
    return;
  }


  e.preventDefault();

  e.stopPropagation();


  const url =
    link.getAttribute('data-ps-data');


  if (url) {

    window.location.href = url;

  }

});















/* =========================================
   FILTROWANIE PRODUKTÓW
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

  const filtersContainer = document.querySelector('#filters-list');

  if (!filtersContainer) {
    return;
  }


  /* =========================================
     CHECKBOXY
     ========================================= */

  const filterInputs = filtersContainer.querySelectorAll(
    'input[type="checkbox"]'
  );


  /* =========================================
     NAZWA FILTRA
     ========================================= */

  function getFilterName(input) {

    return input.name.replace(/\[\]$/, '');

  }


  /* =========================================
     PRZYWRACANIE CHECKBOXÓW Z URL
     ========================================= */

  function restoreFiltersFromUrl() {

    const url = new URL(window.location.href);
    const params = url.searchParams;

    filterInputs.forEach((input) => {

      const name = getFilterName(input);

      const values = params.getAll(name);

      input.checked = values.includes(input.value);

    });

  }


  /* =========================================
     PRZYWRACANIE CENY Z URL
     ========================================= */

  function restorePriceFromUrl() {

    const url = new URL(window.location.href);

    const minParam = url.searchParams.get('price_min');
    const maxParam = url.searchParams.get('price_max');

    const priceSlider = document.querySelector('.price-slider');

    if (!priceSlider) {
      return;
    }

    const minInput = priceSlider.querySelector(
      '.price-slider__input--min'
    );

    const maxInput = priceSlider.querySelector(
      '.price-slider__input--max'
    );

    if (!minInput || !maxInput) {
      return;
    }


    /* =========================================
       MIN CENA
       ========================================= */

    if (minParam !== null) {

      const minValue = Number(minParam);

      if (!isNaN(minValue)) {

        minInput.value = minValue;

      }

    }


    /* =========================================
       MAX CENA
       ========================================= */

    if (maxParam !== null) {

      const maxValue = Number(maxParam);

      if (!isNaN(maxValue)) {

        maxInput.value = maxValue;

      }

    }


    /*
     * Odświeżamy wizualny slider.
     */

    minInput.dispatchEvent(
      new Event('input', {
        bubbles: true
      })
    );

    maxInput.dispatchEvent(
      new Event('input', {
        bubbles: true
      })
    );

  }


  /* =========================================
     POBIERANIE CHECKBOXÓW
     ========================================= */

  function getSelectedFilters() {

    const selected = {};

    filterInputs.forEach((input) => {

      if (!input.checked) {
        return;
      }

      const name = getFilterName(input);

      if (!selected[name]) {
        selected[name] = [];
      }

      selected[name].push(input.value);

    });

    return selected;

  }


  /* =========================================
     USUWANIE STARYCH FILTRÓW
     ========================================= */

  function removeFilterParameters(url) {

    const filterNames = [
      'styl',
      'ksztalt',
      'material',
      'rozmiar',
      'kolor',
      'price_min',
      'price_max'
    ];

    filterNames.forEach((name) => {

      url.searchParams.delete(name);

    });

  }


  /* =========================================
     DODAWANIE CHECKBOXÓW DO URL
     ========================================= */

  function addFilterParameters(url, selectedFilters) {

    Object.keys(selectedFilters).forEach((name) => {

      selectedFilters[name].forEach((value) => {

        url.searchParams.append(
          name,
          value
        );

      });

    });

  }


  /* =========================================
     POBIERANIE CENY
     ========================================= */

  function getSelectedPrice() {

    const priceSlider = document.querySelector('.price-slider');

    if (!priceSlider) {
      return null;
    }

    const minInput = priceSlider.querySelector(
      '.price-slider__input--min'
    );

    const maxInput = priceSlider.querySelector(
      '.price-slider__input--max'
    );

    if (!minInput || !maxInput) {
      return null;
    }

    const minValue = Number(minInput.value);
    const maxValue = Number(maxInput.value);

    const minPrice = Number(minInput.min);
    const maxPrice = Number(minInput.max);


    /*
     * Jeżeli zakres jest domyślny,
     * nie dodajemy ceny do URL.
     */

    if (
      minValue === minPrice &&
      maxValue === maxPrice
    ) {

      return null;

    }


    return {
      min: minValue,
      max: maxValue
    };

  }


  /* =========================================
     START
     ========================================= */

  restoreFiltersFromUrl();

  restorePriceFromUrl();


  /* =========================================
     PRZYCISK "FILTRUJ"
     ========================================= */

  const applyFiltersButton = document.querySelector(
    '#apply-filters'
  );


  if (applyFiltersButton) {

    applyFiltersButton.addEventListener('click', () => {

      const url = new URL(window.location.href);


      /* =========================================
         WRACAMY NA PIERWSZĄ STRONĘ
         ========================================= */

      url.searchParams.delete('page');


      /* =========================================
         POBIERAMY CHECKBOXY
         ========================================= */

      const selectedFilters = getSelectedFilters();


      /* =========================================
         USUWAMY STARE FILTRY
         ========================================= */

      removeFilterParameters(url);


      /* =========================================
         DODAJEMY CHECKBOXY
         ========================================= */

      addFilterParameters(
        url,
        selectedFilters
      );


      /* =========================================
         DODAJEMY CENĘ
         ========================================= */

      const selectedPrice = getSelectedPrice();


      if (selectedPrice) {

        url.searchParams.set(
          'price_min',
          selectedPrice.min
        );

        url.searchParams.set(
          'price_max',
          selectedPrice.max
        );

      }


      /* =========================================
         PRZEJŚCIE NA NOWY URL
         ========================================= */

      window.location.href = url.toString();

    });

  }

});





document.addEventListener('DOMContentLoaded', () => {
    const hero = document.querySelector('.s24-category-hero');

    if (!hero) {
        return;
    }

    const text = hero.querySelector('.s24-category-hero__text');

    if (!text) {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 767px)');

    const createButton = () => {
        if (hero.querySelector('.s24-category-hero__more')) {
            return;
        }

        const button = document.createElement('button');

        button.type = 'button';
        button.className = 's24-category-hero__more';
        button.textContent = 'Czytaj więcej';

        text.insertAdjacentElement('afterend', button);

        button.addEventListener('click', () => {
            const expanded = text.classList.toggle('is-expanded');

            button.textContent = expanded
                ? 'Czytaj mniej'
                : 'Czytaj więcej';
        });
    };

    const removeButton = () => {
        const button = hero.querySelector('.s24-category-hero__more');

        if (button) {
            button.remove();
        }

        text.classList.remove('is-expanded');
    };

    const handleViewportChange = (event) => {
        if (event.matches) {
            createButton();
        } else {
            removeButton();
        }
    };

    handleViewportChange(mobileQuery);

    mobileQuery.addEventListener('change', handleViewportChange);
});





/* =========================================
   AKTYWNE FILTRY
   BEZ CENY
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    const filtersContainer =
        document.querySelector('#filters-list');

    const activeFiltersContainer =
        document.querySelector('#active-filters');

    if (!filtersContainer || !activeFiltersContainer) {
        return;
    }


    /* =========================================
       POBIERANIE NAZWY PARAMETRU
       ========================================= */

    function getFilterName(input) {

        return input.name.replace(/\[\]$/, '');

    }


    /* =========================================
       POBIERANIE AKTYWNYCH FILTRÓW
       ========================================= */

    function getActiveFilters() {

        const activeFilters = [];

        const inputs =
            filtersContainer.querySelectorAll(
                'input[type="checkbox"]'
            );


        inputs.forEach((input) => {

            if (!input.checked) {
                return;
            }


            const filterName =
                getFilterName(input);


            const filterValue =
                input.dataset.filterValue ||
                input.nextElementSibling?.nextElementSibling?.textContent.trim() ||
                input.value;


            activeFilters.push({
                name: filterName,
                value: filterValue,
                input: input
            });

        });


        return activeFilters;

    }


    /* =========================================
       RENDEROWANIE AKTYWNYCH FILTRÓW
       ========================================= */

    function renderActiveFilters() {

        const activeFilters =
            getActiveFilters();


        activeFiltersContainer.innerHTML = '';


        if (!activeFilters.length) {

            activeFiltersContainer.classList.remove(
                'has-filters'
            );

            return;

        }


        activeFiltersContainer.classList.add(
            'has-filters'
        );


        activeFilters.forEach((filter) => {

            const item =
                document.createElement('div');

            item.className =
                'active-filters__item';


            /* =========================================
               PRZYCISK X
               ========================================= */

            const removeButton =
                document.createElement('button');

            removeButton.type = 'button';

            removeButton.className =
                'active-filters__remove';

            removeButton.innerHTML = '&times;';

            removeButton.setAttribute(
                'aria-label',
                'Usuń filtr ' + filter.value
            );


            /* =========================================
               WARTOŚĆ FILTRA
               ========================================= */

            const text =
                document.createElement('span');

            text.className =
                'active-filters__text';

            text.textContent =
                filter.value;


            item.appendChild(removeButton);

            item.appendChild(text);

            activeFiltersContainer.appendChild(item);


            /* =========================================
               USUWANIE POJEDYNCZEGO FILTRA
               ========================================= */

            removeButton.addEventListener(
                'click',
                () => {

                    removeSingleFilter(
                        filter.input
                    );

                }
            );

        });

    }


    /* =========================================
       USUWANIE POJEDYNCZEGO FILTRA
       ========================================= */

    function removeSingleFilter(input) {

        if (!input) {
            return;
        }


        /*
         * Odznaczamy checkbox.
         */

        input.checked = false;


        /*
         * Aktualizujemy listę aktywnych filtrów.
         */

        renderActiveFilters();


        /*
         * Budujemy nowy URL.
         */

        const url =
            new URL(window.location.href);


        /*
         * Nazwa filtra.
         */

        const filterName =
            getFilterName(input);


        /*
         * Pobieramy wszystkie aktualne wartości.
         */

        const currentValues =
            url.searchParams.getAll(filterName);


        /*
         * Usuwamy parametr.
         */

        url.searchParams.delete(filterName);


        /*
         * Dodajemy ponownie wszystkie wartości
         * oprócz tej klikniętej.
         */

        currentValues.forEach((value) => {

            if (value !== input.value) {

                url.searchParams.append(
                    filterName,
                    value
                );

            }

        });


        /*
         * Wracamy na pierwszą stronę.
         */

        url.searchParams.delete('page');


        /*
         * Zmieniamy URL BEZ przeładowania strony.
         */

        window.history.pushState(
            {},
            '',
            url.toString()
        );

    }


    /* =========================================
       START
       ========================================= */

    renderActiveFilters();


    /* =========================================
       AKTUALIZACJA PO ZMIANIE CHECKBOXA
       ========================================= */

    filtersContainer
        .querySelectorAll(
            'input[type="checkbox"]'
        )
        .forEach((input) => {

            input.addEventListener(
                'change',
                () => {

                    renderActiveFilters();

                }
            );

        });

});


/* =========================================
   RESET WSZYSTKICH FILTRÓW
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    const resetButton =
        document.querySelector('#resset');

    const filtersContainer =
        document.querySelector('#filters-list');

    if (!resetButton || !filtersContainer) {
        return;
    }


    resetButton.addEventListener('click', () => {


        /* =========================================
           ODZNACZAMY WSZYSTKIE CHECKBOXY
           ========================================= */

        const filterInputs =
            filtersContainer.querySelectorAll(
                'input[type="checkbox"]'
            );

        filterInputs.forEach((input) => {

            input.checked = false;

        });


        /* =========================================
           RESET SLIDERA CENY
           ========================================= */

        const priceSlider =
            filtersContainer.querySelector(
                '.price-slider'
            );


        if (priceSlider) {

            const minInput =
                priceSlider.querySelector(
                    '.price-slider__input--min'
                );

            const maxInput =
                priceSlider.querySelector(
                    '.price-slider__input--max'
                );


            if (minInput) {

                minInput.value =
                    minInput.min;

                minInput.dispatchEvent(
                    new Event('input', {
                        bubbles: true
                    })
                );

            }


            if (maxInput) {

                maxInput.value =
                    maxInput.max;

                maxInput.dispatchEvent(
                    new Event('input', {
                        bubbles: true
                    })
                );

            }

        }


        /* =========================================
           CZYŚCIMY AKTYWNE FILTRY
           ========================================= */

        const activeFiltersContainer =
            document.querySelector(
                '#active-filters'
            );


        if (activeFiltersContainer) {

            activeFiltersContainer.innerHTML = '';

            activeFiltersContainer.classList.remove(
                'has-filters'
            );

        }


        /* =========================================
           USUWAMY FILTRY Z URL
           ========================================= */

        const url =
            new URL(window.location.href);


        const filterNames = [
            'styl',
            'ksztalt',
            'material',
            'rozmiar',
            'kolor',
            'price_min',
            'price_max',
            'page'
        ];


        filterNames.forEach((name) => {

            url.searchParams.delete(name);

        });


        /* =========================================
           ZMIANA URL BEZ RELOADU
           ========================================= */

   window.location.href = url.toString();

    });

});



</script>