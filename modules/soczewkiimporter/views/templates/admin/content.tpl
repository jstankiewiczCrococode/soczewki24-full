<div class="panel">
  <h3><i class="icon-cloud-download"></i> Soczewki24 Importer</h3>
  <p><strong>Etap 3:</strong> import produktów batchami po 20 sztuk.</p>
  <p>Import nie przechodzi automatycznie do kolejnej strony.</p>
</div>

<div class="panel">
  <h4>Kategorie</h4>
  {function name=renderTree nodes=[] prefix=''}
    <ul style="margin-left:20px">
    {foreach $nodes as $name => $node}
      {if $name[0] != '_'}
      {assign var='path' value=$prefix|cat:$name}
      <li style="margin:4px 0">
        <a href="{$link->getAdminLink('AdminSoczewkiImporter')|escape:'html':'UTF-8'}&category={$path|urlencode}">{$name|escape:'html':'UTF-8'}</a>
        <strong>({$node._count})</strong>
        {if !empty($node._children)}{call name=renderTree nodes=$node._children prefix=$path|cat:' > '}{/if}
      </li>
      {/if}
    {/foreach}
    </ul>
  {/function}
  {call name=renderTree nodes=$si_tree}
</div>

{if $si_category != ''}
<div class="panel">
  <h3>{$si_category|escape:'html':'UTF-8'}</h3>
  <p>Produktów: <strong>{$si_total}</strong> | Batch: <strong>{$si_limit}</strong> | Strona: <strong>{$si_page} / {$si_pages}</strong></p>

  {if $importResult}
    <div class="alert alert-success">
      <strong>Import batcha zakończony.</strong><br>
      Utworzono: {$importResult.created}
      | Zaktualizowano: {$importResult.updated}
      | Błędy: {count($importResult.errors)}
    </div>

    {if !empty($importResult.errors)}
      <div class="alert alert-danger">
        {foreach $importResult.errors as $err}
          <div><strong>{$err.feed_id|escape:'html':'UTF-8'}</strong> — {$err.title|escape:'html':'UTF-8'}: {$err.message|escape:'html':'UTF-8'}</div>
        {/foreach}
      </div>
    {/if}
  {/if}

  <h4>Mapowanie kategorii — batch {$si_page}</h4>
  <p><strong>Tryb kontrolny.</strong> Przed zapisem każda ścieżka musi wskazywać istniejącą kategorię.</p>

  {foreach $itemMap as $map}
    {if $map.status == 'ok'}
      <div style="padding:4px 0;">
        <span style="color:green; font-weight:bold;">✓</span>
        {$map.path|escape:'html':'UTF-8'}
        <strong>→ ID {$map.resolved_id}</strong>
      </div>
    {else}
      <div style="padding:4px 0;">
        <span style="color:#c00; font-weight:bold;">⚠</span>
        {$map.path|escape:'html':'UTF-8'}
      </div>
    {/if}
  {/foreach}

  <hr>

  <table class="table">
    <thead><tr><th>ID</th><th>Produkt</th><th>Marka</th><th>Cena</th><th>Dostępność</th><th>Kategorie feedu</th></tr></thead>
    <tbody>
    {foreach $si_items as $item}
      <tr>
        <td>{$item.id|escape:'html':'UTF-8'}</td>
        <td>{$item.title|escape:'html':'UTF-8'}</td>
        <td>{$item.brand|escape:'html':'UTF-8'}</td>
        <td>{$item.price|escape:'html':'UTF-8'}{if $item.sale_price} / {$item.sale_price|escape:'html':'UTF-8'}{/if}</td>
        <td>{$item.availability|escape:'html':'UTF-8'}</td>
        <td>{foreach $item.categories as $cat}{$cat|escape:'html':'UTF-8'}<br>{/foreach}</td>
      </tr>
    {/foreach}
    </tbody>
  </table>

  <div style="margin-top:15px">
    {if $si_page > 1}
      <a class="btn btn-default" href="{$link->getAdminLink('AdminSoczewkiImporter')}&category={$si_category|urlencode}&page={$si_page-1}">← Poprzednie 20</a>
    {/if}

    {if $si_page < $si_pages}
      <a class="btn btn-default" href="{$link->getAdminLink('AdminSoczewkiImporter')}&category={$si_category|urlencode}&page={$si_page+1}">Następne 20 →</a>
    {/if}

    <form method="post" style="display:inline-block; margin-left:8px;">
      <input type="hidden" name="category" value="{$si_category|escape:'html':'UTF-8'}">
      <input type="hidden" name="page" value="{$si_page}">
      <button type="submit" name="import_batch" value="1" class="btn btn-success" {if $importResult || empty($si_items)}disabled{/if}>
        <i class="icon-download"></i> Importuj batch {$si_page} (20 produktów)
      </button>
    </form>
  </div>
</div>
{/if}
