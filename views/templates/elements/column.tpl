<div id="{$element->getId()}" class="prettyblocks-column col-{$element->getValue('width')} {if $element->getValue('widthTablet')}col-md-{$element->getValue('widthTablet')}{/if} {if $element->getValue('widthMobile')}col-sm-{$element->getValue('widthMobile')}{/if} {$element->getValue('cssClass')}" 
     style="background-color: {$element->getValue('background')}; 
            padding: {$element->getValue('padding')}px; 
            text-align: {$element->getValue('alignment')};">
  
  {foreach from=$children item=component}
    {$component->render() nofilter}
  {/foreach}
</div>