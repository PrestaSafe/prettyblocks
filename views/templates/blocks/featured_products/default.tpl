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
{if $block.extra.products|@count>0}
<section class="featured-products {if $block.settings.default.container} container {/if}" 
{if $block.settings.default.bg_color}style="background: {$block.settings.default.bg_color}"{/if}>
  {if $block.settings.display_title}
    <h2 class="tw_h2 tw_products-section-title tw_uppercase tw_text-center tw_py-2">
      {$block.settings.title}
    </h2>
  {/if}
  {include file="module:prettyblocks/views/templates/blocks/featured_products/productlist.tpl" products=$block.extra.products cssClass="tw_grid tw_grid-cols-1 sm:tw_grid-cols-2 lg:tw_grid-cols-4 tw_gap-4 tw_mb-4"}
  {if $block.settings.display_link}
    <a class="all-product-link tw_text-center tw_h4 tw_float-right " href="{url entity='category' id=$block.settings.category.id}">
      {l s='All products' d='Shop.Theme.Catalog'}<i class="material-icons">&#xE315;</i>
    </a>
    <div class="tw_clear-both"></div>
  {/if}
</section>
{/if}