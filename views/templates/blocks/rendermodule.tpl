{$module_name = $block.settings.module_name}
{if $module_name}
<div class="{if $block.settings.default.container} container {/if}">
        {widget name=$module_name}
    </div>
{/if}

