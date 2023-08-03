<{$tag} data-block-id_prettyblock='{$block.id_prettyblocks}'
data-attributes='{$attributesHTML|escape:'html'}' data-field='{$field}' class='ptb-title {$classes}
{if $attributes.bold} font-weight-bold {/if}
{if $attributes.italic} font-italic {/if}' 
{if isset($index)} data-index="{$index}" {/if}
    style="
        {if $attributes.underline} text-decoration: underline; {/if}
        {if $attributes.size && $attributes.size > 0} font-size: {$attributes.size}px; {/if}
    ">
    {$value nofilter}
</{$tag}>