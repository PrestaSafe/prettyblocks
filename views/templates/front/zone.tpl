{**
 * Since 2021 PrestaSafe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@prestasafe.com so we can send you a copy immediately.
 *
 * @author    PrestaSafe <contact@prestasafe.com>
 * @copyright Since 2021 PrestaSafe
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaSafe
 *}


 <div data-prettyblocks-zone="{$zone_name}">
 {if $blocks|count > 0}
  {foreach from=$blocks item=block name=zoneBlocks}
  <div {if $block.settings.default.load_ajax} load-ajax {/if} data-block data-instance-id="{$block.instance_id}" data-id-prettyblocks="{$block.id_prettyblocks}">
    {if !$block.settings.default.load_ajax}
        {$template = 'module:prettyblocks/views/templates/blocks/welcome.tpl'}
        {if isset($block.templates[$block.templateSelected])}
          {$template = $block.templates[$block.templateSelected]}
        {/if}

        {include file=$template
          instance_id=$block.instance_id
          id_prettyblocks=$block.id_prettyblocks
          block=$block
          states=$block.states}
      {else}  
        Chargement en cours.... 
      {/if}
    </div>
  {/foreach}
{/if}
  </div>
<div class="d-none blocks text-center w-100 p-5" data-zone-name="{$zone_name}"></div>


