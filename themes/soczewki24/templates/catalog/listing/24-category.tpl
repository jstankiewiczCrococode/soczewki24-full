{include file='_partials/head.tpl'}


{block name='content'}

  {block name='product_list_header'}
    <div id="js-product-list-header">
      {include file='catalog/_partials/category-header.tpl' listing=$listing category=$category}
    </div>
  {/block}

  {hook h='displayHeaderCategory'}



  {hook h='displayFooterCategory'}

{/block}


    


    {block name='footer'}
      <footer id="footer" class="footer">
        {include file='_partials/footer.tpl'}
      </footer>
    {/block}