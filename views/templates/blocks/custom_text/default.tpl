{if $block.settings.content}
<div class="{if $block.settings.default.container}container{else}_force-full{/if}">
    {$block.settings.content nofilter}
</div>
{/if}