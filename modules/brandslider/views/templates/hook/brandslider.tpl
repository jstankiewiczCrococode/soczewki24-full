{if $brandslider_manufacturers|count}
    <section class="brandslider">
        <button id="arrow-left">
           <img src="{$module_dir}views/img/arrow-left-white.png" alt="arrow">
        </button>
        <div class="brandslider__items">

            {foreach from=$brandslider_manufacturers item=manufacturer}
                <div class="brandslider__item">

                    <a href="{$link->getManufacturerLink($manufacturer.id_manufacturer)|escape:'htmlall':'UTF-8'}"
                       class="brandslider__link">

                        <img
                            src="{$urls.base_url}img/m/{$manufacturer.id_manufacturer}.jpg"
                            alt="{$manufacturer.name|escape:'htmlall':'UTF-8'}"
                            class="brandslider__logo"
                        >

               

                    </a>

                </div>
            {/foreach}

        </div>
        <button id="arrow-right">
            <img src="{$module_dir}views/img/arrow-right-white.png" alt="arrow">
        </button>
    </section>

    <div class="brandslider__dots">
    <button
        type="button"
        class="brandslider__dot is-active"
        data-slider-dot="0"
        aria-label="Slajd 1"
    ></button>

    <button
        type="button"
        class="brandslider__dot"
        data-slider-dot="1"
        aria-label="Slajd 2"
    ></button>
</div>
{/if}


<script src="{$urls.base_url}modules/brandslider/views/js/brandslider.js"></script>