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
<div class="home-popular-categories {if $block.settings.mgt}mgt{/if} {if $block.settings.default.container} container {/if} ">
  <div class="row">
    <div class="col-xl-8 col-lg-9" >
      <div class="card border-0 box-shadow-lg">
        <div class="card-body px-3 pt-grid-gutter pb-0"
          style="{if $block.settings.default.bg_color} background-color: {$block.settings.default.bg_color} {/if}">
          <div class="row no-gutters pl-1">
          {foreach $block.states as $s}
           <div class="col-sm-4 px-2 mb-grid-gutter">
              <a class="d-block text-center text-decoration-none mr-1" href="#">              
                {if $s.upload.url}
                  <img class="d-block rounded mb-3 mx-auto" src="{$s.upload.url}" alt="ALT" loading="lazy">
                {/if}
                <p class="h3 font-size-base pt-1 mb-0">  {$s.title} </p>
              </a>
            </div>
            {/foreach}  
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
