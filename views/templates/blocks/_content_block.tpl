{capture name="image_to_side"}
    {$image = $link->getCatImageLink('category_default', $category.id)}
    <div class="col-lg-5">
      <div class="d-flex flex-column h-100 overflow-hidden rounded-lg" style="background-color: #e2e9ef;">
        <div class="d-flex justify-content-between px-grid-gutter py-grid-gutter">
          <div>
            <h3 class="mb-1">
              {if $title}{$title}{else}{$category.name}{/if}
            </h3>
            <a class="font-size-md" href="{url entity='category' id=$category.id}">{l s='View all' d='Shop.Theme.Actions'} <i class="czi-arrow-right font-size-xs align-middle ml-1"></i></a>
          </div>
          {capture name="container_id_rand"}homecategory-carousel-{time()}{/capture}
          <div class="cz-custom-controls" id="{$smarty.capture.container_id_rand}">
            <button type="button"><i class="czi-arrow-left"></i></button>
            <button type="button"><i class="czi-arrow-right"></i></button>
          </div>
        </div>
        {$image = $link->getCatImageLink('category_default', $category.id)}
        {if Configuration::get('CZ_CATEGORYHOME_IMAGE')}
          {capture name="CZ_CATEGORYHOME_IMAGE"}{$urls.base_url}modules/cz_homecategory/views/images/{Configuration::get('CZ_CATEGORYHOME_IMAGE')}{/capture}
          {$image = $smarty.capture.CZ_CATEGORYHOME_IMAGE}
        {/if}
        {if !$use_custom_image}
        <a class="d-none d-lg-block mt-auto" href="{url entity='category' id=$category.id}">
          <img class="d-block w-100" src="{$image}" loading="lazy" alt="{$category.name}">
        </a>
        {else isset($override_image.url)}
          <a class="d-none d-lg-block mt-auto" href="{url entity='category' id=$category.id}">
          <img class="d-block w-100" src="{$override_image.url}" loading="lazy" alt="{$category.name}">
        </a>
        {/if}
      </div>
    </div>
  {/capture}
    {if !$image_side}
        {$smarty.capture.image_to_side nofilter}
    {/if}
  <div class="col-lg-7 pt-4 pt-lg-0 {if $image_side} sm-order-2 {/if}">
      <div class="cz-carousel">
        <div class="cz-carousel-inner" data-carousel-options='{literal}{"nav": false, "gutter": 16, "controlsContainer": "#{/literal}{$smarty.capture.container_id_rand}{literal}"}{/literal}'>
          <div>
            <div class="row m-n2">
              {foreach from=$products key="k" item="product"}
                {if $k % 6 == 0 && $k > 1}
                  </div></div><div><div class="row m-n2">
                {/if}
                <div class="col-6 col-md-4 p-2">
                  {include file='catalog/_partials/miniatures/product.tpl' product=$product carousel=true}
                </div>
              {/foreach}
            </div>
          </div>
        </div>
      </div>
    </div>

    {if $image_side}
      {$smarty.capture.image_to_side nofilter}
    {/if}
