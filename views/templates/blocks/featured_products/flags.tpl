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
 {block name='product_flags'}
    <ul class="tw_flex tw_space-x-2 tw_absolute tw_ml-2 tw_mt-2">
        {foreach from=$product.flags item=flag}
            <li class="tw_px-1 tw_py-0.5 tw_bg-orange-500 tw_text-white tw_rounded-md">{$flag.label}</li>
        {/foreach}
    </ul>
{/block} 
