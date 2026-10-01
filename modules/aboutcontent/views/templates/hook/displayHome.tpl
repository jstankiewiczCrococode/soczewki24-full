<section id="about">
    <div class="container">
    <div class="content">

        <h3>{$title}</h3>

        <div class="description">
            {$description nofilter}
        </div>

        <div class="cols">

            <div class="col">
                <h6>{$left_title}</h6>
                <div class="text">
                    {$left_text nofilter}
                </div>
            </div>

            <div class="col">
                <h6>{$right_title}</h6>
                <div class="text">
                    {$right_text nofilter}
                </div>
            </div>

        </div>

    </div>

    {if $image}
        <img src="{$image}" alt="{$image_alt|escape:'htmlall':'UTF-8'}">
    {/if}
</div>
</section>