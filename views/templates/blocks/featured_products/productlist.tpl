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

{capture assign="productClasses"}{if !empty($productClass)}{$productClass}{else}col-xs-12 col-sm-6 col-xl-4{/if}{/capture}
<div class="{if !empty($cssClass)} {$cssClass}{/if}">
{foreach from=$products item="product" key="position"}
    {include file="module:prettyblocks/views/templates/blocks/featured_products/product.tpl" product=$product position=$position productClasses=$productClasses}
{/foreach}
</div>
