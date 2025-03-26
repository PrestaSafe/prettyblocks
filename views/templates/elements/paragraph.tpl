<div id="{if $element->getValue('enableCustomId')}{$element->getValue('customId')}{else}{$element->getId()}{/if}" 
     class="prettyblocks-paragraph {if $element->getValue('dropCap')}drop-cap{/if} {$element->getValue('cssClass')}" 
     style="color: {$element->getValue('textColor')}; 
            font-size: {if $element->getValue('fontSize') == 'small'}0.875rem{elseif $element->getValue('fontSize') == 'large'}1.25rem{elseif $element->getValue('fontSize') == 'xlarge'}1.5rem{else}1rem{/if}; 
            font-weight: {if $element->getValue('fontWeight') == 'light'}300{elseif $element->getValue('fontWeight') == 'medium'}500{elseif $element->getValue('fontWeight') == 'semibold'}600{elseif $element->getValue('fontWeight') == 'bold'}700{else}400{/if}; 
            text-align: {$element->getValue('alignment')}; 
            line-height: {if $element->getValue('lineHeight') == 'compact'}1.2{elseif $element->getValue('lineHeight') == 'relaxed'}1.8{elseif $element->getValue('lineHeight') == 'loose'}2{else}1.5{/if};">
  
  {$element->getValue('content')}
</div>