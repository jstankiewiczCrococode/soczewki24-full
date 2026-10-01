<div class="s24-footer-configurable">

    <div class="s24-footer-columns">

        {foreach from=$s24FooterColumns item=column}
            <div class="s24-footer-column">

                <h3>
                    {$column.name|escape:'htmlall':'UTF-8'}
                </h3>

                {if !empty($column.items)}
                    <ul>
                        {foreach from=$column.items item=item}
                            <li>
                                <a href="{$item.url|escape:'htmlall':'UTF-8'}">
                                    {$item.label|escape:'htmlall':'UTF-8'}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                {/if}

            </div>
        {/foreach}

    </div>

</div>