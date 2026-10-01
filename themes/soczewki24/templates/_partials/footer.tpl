
<section class="container" >

    <div class="newsletter">

      <div class="col">

        <p class="title" >Zapisz się do newslettera</3>

        <p class="bottom_desc">
            Zapisując się do newslettera wyrażasz zgodę
            na otrzymywanie ofert marketingowych.
        </p>

        </div>


        <div class="logo-col">
            {if !empty($s24NewsletterLogo)}
                <img
                    src="{$s24NewsletterLogo|escape:'htmlall':'UTF-8'}"
                    alt="Soczewki24"
                >
            {/if}
        </div>

    </div>


  <div class="s24-footer-columns">

{if !empty($s24FooterColumns)}

    {foreach from=$s24FooterColumns item=column}

        <div class="col">

            <h6>
                {$column.name|escape:'htmlall':'UTF-8'}
            </h6>

            {if !empty($column.items)}

                <div class="links">

              {foreach from=$column.items item=item}

                {if $item.type == 'tel'}

                    <a href="tel:{$item.url|escape:'htmlall':'UTF-8'}">
                        {$item.label|escape:'htmlall':'UTF-8'}
                    </a>

                {elseif $item.type == 'email'}

                    <a href="mailto:{$item.url|escape:'htmlall':'UTF-8'}">
                        {$item.label|escape:'htmlall':'UTF-8'}
                    </a>

                {else}

                    <a href="{$item.url|escape:'htmlall':'UTF-8'}">
                        {$item.label|escape:'htmlall':'UTF-8'}
                    </a>

                {/if}

            {/foreach}

                </div>

            {/if}

        </div>

    {/foreach}

{/if}

  </div>

    <div class="locations">

        <p>Odwiedź nasze salony w:</p>

        <div class="list">

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>
            <div class="separator"></div>

            <a>Optyk Aleja Bielany, Wrocław</a>

        </div>

    </div>


    <hr>


    <div class="bottom">

        <div class="col">

            <p>© 2026 Soczewki24. All rights reserved.</p>

            <div class="links">
                <a href="">Regulamin</a>
                <div class="separator"></div>
                <a href="">Polityka prywatności</a>
                <div class="separator"></div>              
                <a href="">Polityka cookies</a>
                <div class="separator"></div>              
                <a href="">Wysyłka i zwroty</a>
            </div>

        </div>


        <div class="socjal-icons">

            {if !empty($s24FooterSocials)}

                {foreach from=$s24FooterSocials item=social}

                    {if !empty($social.icon)}

                        <a
                            href="{$social.url|escape:'htmlall':'UTF-8'}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <img
                                src="{$social.icon|escape:'htmlall':'UTF-8'}"
                                alt="{$social.type|escape:'htmlall':'UTF-8'}"
                            >
                        </a>

                    {/if}

                {/foreach}

            {/if}

        </div>

    </div>

</section>

