{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{extends file=$layout}

{block name='breadcrumb'}{/block}

{block name='content_columns'}
  {block name='left_column'}{/block}

  {block name='content_wrapper'}
    <div id="center-column" class="center-column page">
      {hook h="displayContentWrapperTop"}

      {block name='content'}
        {block name='page_content_container'}
          <div id="content" class="page-content page-content--home">
          
              {include file='components/home/slider.tpl'}
	            {hook h='displayHomeItems'}


              <section id="SectionCarusel">
                  <div class="container">
                    <div class="top">
                      <h5>Promocje</h5>
                      <a href="" >
                      <span>Sprawdź więcej<span>
                      <img src="{$urls.theme_assets}img-dist/arrow.png" alt="arrow">
                      </a>
                    </div>
                  </div>
              </section>

          

              {hook h='displayLensFinder'}

              <section id="SectionCarusel">
                  <div class="container">
                    <div class="top">
                      <h5>Bestesellery</h5>
                      <a href="" >
                      <span>Sprawdź więcej<span>
                      <img src="{$urls.theme_assets}img-dist/arrow.png" alt="arrow">
                      </a>
                    </div>
                  </div>
              </section>



      
              
              <section id="product-carusel">
                <div class="container">
                  <div class="top">
                    <h5>Strefa marek</h5>
                    <a href="" >
                      <span>Sprawdź więcej<span>
                      <img src="{$urls.theme_assets}img-dist/arrow.png" alt="arrow">
                    </a>
                  </div>
                  {hook h='displayBrandSlider'}
                  </div>
              </section>

            {assign var='aboutModule' value=Module::getInstanceByName('aboutcontent')}
            {$aboutModule->renderAbout() nofilter}
          </div>
        {/block}
      {/block}

    
      {hook h='displayBlogSection'}
      
      {hook h="displayContentWrapperBottom"}
    </div>
  {/block}

{/block}
