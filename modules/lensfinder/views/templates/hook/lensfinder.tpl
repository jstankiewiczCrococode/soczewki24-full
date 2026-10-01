<section id="lensfinder">

    <div class="container">

    <div class="search">

        <h3>Znajdź soczewki</h3>

        <p>
            Wyszukaj soczewki, które będą odpowiednie dla Ciebie
        </p>

        <form class="form">

            {* Moc *}
            <div class="field">
                <label for="lensfinder-moc">
                    {$lensfinder_config.moc.name|escape:'htmlall':'UTF-8'}
                </label>

                <select
                    id="lensfinder-moc"
                    name="moc"
                >
                    <option value="">
                        {$lensfinder_config.moc.placeholder|escape:'htmlall':'UTF-8'}
                    </option>

                    {foreach from=$lensfinder_config.moc.options item=option}
                        <option value="{$option.value|escape:'htmlall':'UTF-8'}">
                            {$option.label|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>


            {* Krzywizna *}
            <div class="field">
                <label for="lensfinder-krzywizna">
                    {$lensfinder_config.krzywizna.name|escape:'htmlall':'UTF-8'}
                </label>

                <select
                    id="lensfinder-krzywizna"
                    name="krzywizna"
                >
                    <option value="">
                        {$lensfinder_config.krzywizna.placeholder|escape:'htmlall':'UTF-8'}
                    </option>

                    {foreach from=$lensfinder_config.krzywizna.options item=option}
                        <option value="{$option.value|escape:'htmlall':'UTF-8'}">
                            {$option.label|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>


            {* Tryb wymiany *}
            <div class="field">
                <label for="lensfinder-tryb-wymiany">
                    {$lensfinder_config.tryb_wymiany.name|escape:'htmlall':'UTF-8'}
                </label>

                <select
                    id="lensfinder-tryb-wymiany"
                    name="tryb_wymiany"
                >
                    <option value="">
                        {$lensfinder_config.tryb_wymiany.placeholder|escape:'htmlall':'UTF-8'}
                    </option>

                    {foreach from=$lensfinder_config.tryb_wymiany.options item=option}
                        <option value="{$option.value|escape:'htmlall':'UTF-8'}">
                            {$option.label|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>


            {* Typ korekcji *}
            <div class="field">
                <label for="lensfinder-typ-korekcji">
                    {$lensfinder_config.typ_korekcji.name|escape:'htmlall':'UTF-8'}
                </label>

                <select
                    id="lensfinder-typ-korekcji"
                    name="typ_korekcji"
                >
                    <option value="">
                        {$lensfinder_config.typ_korekcji.placeholder|escape:'htmlall':'UTF-8'}
                    </option>

                    {foreach from=$lensfinder_config.typ_korekcji.options item=option}
                        <option value="{$option.value|escape:'htmlall':'UTF-8'}">
                            {$option.label|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>


<button type="submit" class="button">
    <img src="{$urls.base_url}modules/lensfinder/views/img/search.png" alt="">
    <span>Szukaj</span>
</button>


        </form>

    </div>
</div>
</section>