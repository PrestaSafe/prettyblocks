<div id="{$idModal}Modal" class="modalCusto clearfix {$defaultModalClass}">
    <div class="col-xl-4 col-lg-5 col-md-5 col-sm-6 col-xs-12">
        {* {include file="./elem/wireframe_{$idModal}.tpl"} *}
    </div>
    <div class="col-xl-8 col-lg-7 col-md-7 col-sm-6 col-xs-12 sticky">
        <div class="row">
            <div class="col-xl-1 col-lg-1 col-md-0 col-sm-0"></div>
            <div class="col-xl-11 col-lg-12 col-md-12 col-sm-12 col-xs-12 module-list">
                {foreach from=$elementsList key=categoryname item=categories name=cat}
                    <div class="row configuration-rectangle">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 js-module-name js-title-{$categoryname}" data-module_name="{$categoryname}">
                            <span class="col-lg-11 col-sm-11 col-xs-11 col-md-11">
                                {$listCategories[$categoryname]}
                            </span>
                            <span class="col-lg-1 col-sm-1 col-xs-1 col-md-1 configuration-rectangle-caret">
                                <i class="material-icons down">keyboard_arrow_down</i>
                                <i class="material-icons up">keyboard_arrow_up</i>
                            </span>
                        </div>
                        {foreach from=$categories key=type item=elements}
                            {if $type == 'pages'}
                                {foreach from=$elements item=page}
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 module-informations">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">
                                                {if $page.name == 'AdminCategories'}
                                                    <img src="{$moduleImgUri}category_page_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminCmsContent'}
                                                    <img src="{$moduleImgUri}cms_page_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminAttributesGroups'}
                                                    <img src="{$moduleImgUri}manage_attributes_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminManufacturers'}
                                                    <img src="{$moduleImgUri}brands_page_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminProducts'}
                                                    <img src="{$moduleImgUri}manage_catalog_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'admin_product_catalog'}
                                                    <img src="{$moduleImgUri}manage_catalog_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'admin_stock_overview'}
                                                    <img src="{$moduleImgUri}display_stock_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminStockManagement'}
                                                    <img src="{$moduleImgUri}display_stock_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminStores'}
                                                    <img src="{$moduleImgUri}shop_ino_link_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'admin_product_preferences'}
                                                    <img src="{$moduleImgUri}display_product_feature.png" class="img-fluid module-logo">
                                                {else}
                                                    <i class="icon-cogs"></i>
                                                {/if}
                                            </div>
                                            <div class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <b>{$page.displayName}</b>
                                                </div>
                                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-7 description">
                                                    {$page.description}
                                                </div>
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-4 col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-xs-offset-1 general-action">
                                                    <a class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-radius-right btn btn-primary-reverse btn-outline-primary light-button" href="{$page.url}">
                                                        {l s='Configure' mod='ps_themecusto'}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {else if $type == 'sfRoutePages'}
                                {foreach from=$elements item=page}
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 module-informations">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">
                                                {if $page.name == 'AdminCategories'}
                                                    <img src="{$moduleImgUri}category_page_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminCmsContent'}
                                                    <img src="{$moduleImgUri}cms_page_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminAttributesGroups'}
                                                    <img src="{$moduleImgUri}manage_attributes_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminManufacturers'}
                                                    <img src="{$moduleImgUri}brands_page_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'admin_product_catalog'}
                                                    <img src="{$moduleImgUri}manage_catalog_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'admin_stock_overview'}
                                                    <img src="{$moduleImgUri}display_stock_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'AdminStores'}
                                                    <img src="{$moduleImgUri}shop_ino_link_feature.png" class="img-fluid module-logo">
                                                {else if $page.name == 'admin_product_preferences'}
                                                    <img src="{$moduleImgUri}display_stock_feature.png" class="img-fluid module-logo">
                                                {else}
                                                    <i class="icon-cogs"></i>
                                                {/if}
                                            </div>
                                            <div class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <b>{$page.displayName}</b>
                                                </div>
                                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-7 description">
                                                    {$page.description}
                                                </div>
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-4 col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-xs-offset-1 general-action">
                                                    <a class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-radius-right btn btn-primary-reverse btn-outline-primary light-button" href="{$page.url}">
                                                        {l s='Configure' mod='ps_themecusto'}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {else}
                                {foreach from=$elements item=module name=mods}
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 module-informations">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                                            <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">
                                                <img class="module-logo" src="{$ps_uri|cat:$module.logo}"/>
                                            </div>
                                            <div class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <b>{$module.displayName}</b>
                                                </div>
                                                <div class="col-lg-8 col-md-8 col-sm-8 col-xs-7 description">
                                                    {$module.description}
                                                </div>
                                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-4 col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-xs-offset-1">
                                                    {* {include file="./elem/module_actions.tpl"} *}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        {foreachelse}
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 module-informations">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">
                                        <i class="material-icons hidden-xs">extension</i>
                                    </div>
                                    <div class="col-lg-11 col-md-11 col-sm-11 col-xs-11">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <b>{l s='There is no module for this section' mod='ps_themecusto'}</b>
                                        </div>
                                        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-7 description">
                                            {l s='You can install a module for this section from our Modules Selection' mod='ps_themecusto'}
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-4 col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-xs-offset-1 general-action">
                                            <a class="col-lg-12 col-md-12 col-sm-12 col-xs-12 no-radius-right btn btn-primary-reverse btn-outline-primary light-button" href="{$selectionModulePage}" >
                                                {l s='See modules selection' mod='ps_themecusto'}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                {/foreach}

                {* <div class="row">
                    <div class="col-lg-4 col-lg-offset-8">
                        <a class="btn btn-primary btn-lg btn-block" href="{$installedModulePage}#theme_modules">
                            {l s='See all theme\'s modules' mod='ps_themecusto'}
                        </a>
                    </div>
                </div> *}
            </div>
        </div>
    </div>
</div>
