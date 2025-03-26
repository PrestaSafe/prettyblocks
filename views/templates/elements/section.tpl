<section id="{$element->getId()}" class="prettyblocks-section {if $element->getValue('fullWidth')}full-width{/if} {$element->getValue('cssClass')}" 
         style="background-color: {$element->getValue('background')}; 
                padding: {$element->getValue('paddingTop')}px {$element->getValue('paddingRight')}px {$element->getValue('paddingBottom')}px {$element->getValue('paddingLeft')}px;">
  
  {if $element->getValue('container')}
    <div class="container">
  {/if}
  
  {if $element->getValue('title')}
    <h2 class="section-title">{$element->getValue('title')}</h2>
  {/if}
  
  <div class="row">
    {foreach from=$children item=column}
      {$column->render() nofilter}
    {/foreach}
  </div>
  
  {if $element->getValue('container')}
    </div>
  {/if}
</section>