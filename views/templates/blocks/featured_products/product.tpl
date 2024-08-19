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
 {block name='product_miniature_item'}
 <article class="tw_border tw_shadow tw_rounded-md tw_max-w-sm tw_w-full tw_mx-auto tw_pb-2 tw_mb-2" data-id-product="{$product.id_product|escape:'htmlall':'UTF-8'}" data-id-product-attribute="{$product.id_product_attribute|escape:'htmlall':'UTF-8'}">
   {include file='module:prettyblocks/views/templates/blocks/featured_products/flags.tpl'}
   <a class="tw_flex tw_justify-center" href="{$product.url|escape:'htmlall':'UTF-8'}">
     {if $product.cover}
       <img class="tw_d-block tw_w-full tw_rounded-tl-lg tw_rounded-tr-lg" src="{$product.cover.bySize.home_default.url|escape:'htmlall':'UTF-8'}" loading="lazy" alt="{if !empty($product.cover.legend)}{$product.cover.legend|escape:'htmlall':'UTF-8'}{else}{$product.name|escape:'htmlall':'UTF-8'}{/if}">
     {else}
       <img class="tw_d-block tw_w-full tw_rounded-tl-lg tw_rounded-tr-lg" src="{$urls.no_picture_image.bySize.home_default.url|escape:'htmlall':'UTF-8'}" loading="lazy" alt="">
     {/if} 
   </a>
   <div class="tw_text-center tw_mt-4">
     <a class="tw_text-lg tw_font-semibold" href="{url entity='category' id=$product.id_category_default}">{$product.category_name}</a>
     {if $page.page_name == 'index'}
       <p class="tw_text-lg tw_font-semibold"><a href="{$product.url|escape:'htmlall':'UTF-8'}">{$product.name|escape:'htmlall':'UTF-8'}</a></p>
     {else}
       <p class="tw_text-lg tw_font-semibold"><a href="{$product.url|escape:'htmlall':'UTF-8'}">{$product.name|escape:'htmlall':'UTF-8'}</a></p>
     {/if}
     <div class="tw_mt-1">
       <div class="product-price">
         {if $product.availability == 'unavailable' && $product.availability_message}
           <span class="tw_text-md">{$product.availability_message|escape:'htmlall':'UTF-8'}</span>
         {elseif $product.show_price}
           <p class="tw_text-lg tw_text-red-600">{$product.price|escape:'htmlall':'UTF-8'}</p>
           {if $product.has_discount}
             <p class="tw_text-md tw_line-through">{$product.regular_price|escape:'htmlall':'UTF-8'}</p>
           {/if}
         {/if}
       </div>
       {hook h='displayProductListReviews' product=$product}
     </div>
   </div>
   {if empty($carousel)}
     <div class="tw_px-3 tw_w-full tw_mx-auto tw_text-center">
       {if (!isset($configuration.is_catalog) || !$configuration.is_catalog) && $product.add_to_cart_url}
         <form action="{$urls.pages.cart|escape:'htmlall':'UTF-8'}" class="" method="post">
           <input type="hidden" name="token" value="{$static_token|escape:'htmlall':'UTF-8'}">
           <input type="hidden" name="id_product" value="{$product.id|escape:'htmlall':'UTF-8'}">
           <input type="hidden" name="qty" value="{$product.minimal_quantity|escape:'htmlall':'UTF-8'}">
           <button class="tw_bg-blue-500 hover:tw_bg-blue-700 tw_px-2 tw_text-white tw_font-bold tw_py-2  tw_rounded tw_border-0 tx_mx-auto tw_text-center" type="submit" data-button-action="add-to-cart">
            {l s='Add to cart' d='Shop.Theme.Actions'}
           </button>
         </form>
       {else}
         <a class="tw_bg-blue-500 hover:tw_bg-blue-700 tw_text-white tw_px-2 tw_font-bold tw_px-4 tw_rounded tw_border-0 tx_mx-auto tw_text-center" href="{$product.url|escape:'htmlall':'UTF-8'}">{l s='View details' d='Shop.Theme.Actions'}</a>
       {/if}
     </div>
   {/if}
 </article>
{/block}
