<section id="about">

    <div class="content">

        <h3>
            {$title|escape:'htmlall':'UTF-8'}
        </h3>

        {if $description}
            <div class="description">
                {$description nofilter}
            </div>
        {/if}

        <div class="cols">

            <div class="col">

                {if $left_title}
                    <h6>
                        {$left_title|escape:'htmlall':'UTF-8'}
                    </h6>
                {/if}

                {if $left_text}
                    <div class="text">
                        {$left_text nofilter}
                    </div>
                {/if}

            </div>

            <div class="col">

                {if $right_title}
                    <h6>
                        {$right_title|escape:'htmlall':'UTF-8'}
                    </h6>
                {/if}

                {if $right_text}
                    <div class="text">
                        {$right_text nofilter}
                    </div>
                {/if}

            </div>

        </div>

    </div>

    {if $image}
        <img
            src="{$image|escape:'htmlall':'UTF-8'}"
            alt="{$image_alt|escape:'htmlall':'UTF-8'}"
        >
    {/if}

</section>