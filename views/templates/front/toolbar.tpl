<button id="toggle-prettyblocks"></button>
<div id="modal-prettyblocks">
    <div class="header">
        <span>PrettyBlocks</span>
        <div class="close-pb">
        </div>
    </div>
    <div class="wrapper">
        {if isset($prettyblocks.cms)}
        <ul>
            <strong>Pages CMS disponibles</strong>

            {foreach from=$prettyblocks.cms item=link}
                <li>
                    <a href="{$link.link}">{$link.meta_title}</a>
                </li>
            {/foreach}
        </ul>
        {/if}
    </div>
</div>
