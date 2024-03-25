{if $block.settings.image} 
<div class="{if $block.settings.alignment}text-sm-{$block.settings.alignment}{/if} d-block w-full">
    <img src="{$block.settings.image.url}" />
</div>
{/if}