{**
 * Wymiary rozpoznajemy po NAZWIE cechy (maly case), bo ID cech sa rozne w kazdym
 * srodowisku. Zmiana nazwy cechy w BO wymaga zmiany tutaj. Ta zmiana do rozspatrzenia.
 *}
{assign var=dimensionFeatures value=[
  'szerokość soczewki' => 'dimension-lens',
  'szerokość mostka' => 'dimension-bridge',
  'długość zausznika' => 'dimension-temple'
]}

{if $product.grouped_features}
  {if $part == 'attributes'}
    {capture name='attributeItems'}
      {foreach from=$product.grouped_features item=feature}
        {assign var=featureKey value=$feature.name|lower}
        {if !isset($dimensionFeatures[$featureKey])}
          <div class="pdp-attributes__item">
            <dt class="pdp-attributes__label">{$feature.name}</dt>
            <dd class="pdp-attributes__value">{$feature.value|regex_replace:"/\s*\n\s*/":", "}</dd>
          </div>
        {/if}
      {/foreach}
    {/capture}

    {if $smarty.capture.attributeItems|trim}
      <dl class="pdp-attributes">{$smarty.capture.attributeItems nofilter}</dl>
    {/if}
  {elseif $part == 'dimensions'}
    {capture name='dimensionItems'}
      {foreach from=$product.grouped_features item=feature}
        {assign var=featureKey value=$feature.name|lower}
        {if isset($dimensionFeatures[$featureKey])}
          <li class="pdp-dimensions__item">
            <span class="pdp-dimensions__icon" aria-hidden="true">{include file="components/icons/`$dimensionFeatures[$featureKey]`.svg"}</span>
            <span class="pdp-dimensions__text">
              <span class="pdp-dimensions__label">{$feature.name|lower}</span>
              <span class="pdp-dimensions__value">{$feature.value} [mm]</span>
            </span>
          </li>
        {/if}
      {/foreach}
    {/capture}

    {if $smarty.capture.dimensionItems|trim}
      <section class="pdp-dimensions" aria-labelledby="pdp-dimensions-title">
        <h2 class="pdp-dimensions__title" id="pdp-dimensions-title">{l s='Wymiary produktu' d='Shop.Theme.Catalog'}</h2>
        <ul class="pdp-dimensions__list list-unstyled">{$smarty.capture.dimensionItems nofilter}</ul>
      </section>
    {/if}
  {/if}
{/if}
