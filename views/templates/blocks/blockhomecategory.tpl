{**
 * Since 2020 PrestaSafe
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
 * @copyright Since 2020 PrestaSafe
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaSafe
 *}

<div class="cz-homecategory order-4 my-5 {if $block.settings.default.container} container {/if}">
 <div class="row">
   {$category = $block.settings.category}
   {$products = $block.extra.products}
   {if $products && $category}
      {include 
      products=$block.extra.products 
      title=$block.settings.title
      override_image=$block.settings.upload
      use_custom_image=$block.settings.use_custom_image
      category=$category 
      image_side=true file="module:prettyblocks/views/templates/blocks/_content_block.tpl"}
   {/if}

 </div>
</div>
