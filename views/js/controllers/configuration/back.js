/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

'use strict';

let resetActiveCategory = function() {
    $('#psthemecusto .js-wireframe div').removeClass('active');
    $('#psthemecusto .js-wireframe div .hover-element').addClass('hide');
    $('#psthemecusto .js-wireframe div .active-element').addClass('hide');
    $('#psthemecusto .js-wireframe div .out-element').removeClass('hide');
    $('#psthemecusto .js-module-name').removeClass('active');
    $('#psthemecusto .js-module-name').parent('.configuration-rectangle').removeClass('active');
    $('#psthemecusto .js-module-name').parent('.configuration-rectangle').find('.module-informations').slideUp();
    $('#psthemecusto .configuration-rectangle-caret .material-icons.up').hide();
    $('#psthemecusto .configuration-rectangle-caret .material-icons.down').show();
}

let setActiveCategory = function(elem) {
    let module = elem.data('module_name');
    let $moduleInformations = $('.js-title-'+module).parent('.configuration-rectangle').find('.module-informations');
    $('.js-img-'+module).addClass('active');
    $('.js-img-'+module+' .active-element').removeClass('hide');
    $('.js-img-'+module+' .out-element').addClass('hide');
    $('.js-title-'+module).addClass('active');
    $('.js-title-'+module).parent('.configuration-rectangle').addClass('active');
    $moduleInformations.slideDown();

    let checkIfAnimIsDone = setInterval(function() {
        if ($moduleInformations.last().is(':visible')) {
            clearInterval(checkIfAnimIsDone);
            $('html, body').animate({scrollTop: $('.configuration-rectangle.active').position().top}, 1000);
        }
    }, 300);

    $('.js-title-'+module+' .material-icons.up').show();
    $('.js-title-'+module+' .material-icons.down').hide();
}

let ajaxActionModule = function(action, id_module, name) {
    if (typeof action != "undefined"
        && typeof id_module != "undefined"
        && typeof name != "undefined"
    ) {
        $.ajax({
            type: 'POST',
            url: admin_module_ajax_url_psthemecusto,
            data: {
                ajax : true,
                action : 'UpdateModule',
                id_module : id_module,
                module_name : name,
                action_module : action
            },
            beforeSend : function(data) {
                $('.src_loader_'+name).show();
                $('.src_parent_'+name).hide();
            },
            success : function(data) {
                $('.src_parent_'+name).html(data);
                $.growl.notice({ title: "Notice!", message: module_action_sucess});
                $('.src_loader_'+name).hide();
                $('.src_parent_'+name).show();
            },
            error : function(data) {
                $('.src_loader_'+name).hide();
                $('.src_parent_'+name).show();
                $.growl.error({ title: "Notice!", message: module_action_failed });
            }
        });
    }
}

let onClickModalBtn = function(event) {
    resetActiveCategory();

    $('#psthemecusto .btn.btn-primary').removeClass('selected');
    $(this).addClass('selected');

    $('#psthemecusto .modalCusto').addClass('hide');

    let idModalName = $(this).data('id-modal');
    $('#'+idModalName).removeClass('hide');
    let element = $('#'+idModalName+' .js-module-name')[0];

    if (idModalName == 'categoryModal') {
        element = $('#'+idModalName+' .js-title-categories')[0];
    } else if (idModalName == 'productModal') {
        element = $('#'+idModalName+' .js-title-product_management')[0];
    }

    setActiveCategory($(element));
}

let onClickButtonThemeCusto = function (event) {
    event.preventDefault();
    let action = $(this).parent('div').data('action');
    let name = $(this).parent('div').data('module_name');
    let displayName = $(this).parent('div').data('module_displayname');
    let url = $(this).parent('div').prop('action');
    let id_module = $('.src_parent_'+name).data('id_module');

    if (action == 'uninstall' || action == 'disable' || action == 'reset') {
        $('.modal .action_available').hide();
        $('.modal .'+action).show();
        $('.modal .modal-footer a').prop('href', url).attr('data-name', name).attr('data-action', action);
        $('.modal .module-displayname').html(displayName);
    } else {
        ajaxActionModule(action, id_module, name);
    }
}

let onClickWireframeDivORModuleName = function (event) {
    if ($(this).hasClass('active')) {
        resetActiveCategory();
        $(this).removeClass('active');
    } else {
        resetActiveCategory();
        setActiveCategory($(this));
        $(this).addClass('active');
    }
}

let onClickModalFooterLink = function (event) {
    event.preventDefault();
    let name = $(this).attr('data-name');
    let action = $(this).attr('data-action');
    let id_module = $('.src_parent_'+name).data('id_module');
    ajaxActionModule(action, id_module, name);
}

$(document).ready(function() {
    $(document)
        .on('click', "#psthemecusto .panel-heading .btn.btn-primary", onClickModalBtn)
        .on('click', ".modal .modal-footer a", onClickModalFooterLink)
        .on('click', "#psthemecusto .js-wireframe div[class*='js-img-'], #psthemecusto .js-module-name", onClickWireframeDivORModuleName)
        .on('click', "#psthemecusto button", onClickButtonThemeCusto);

    let element = $('.js-module-name')[0];
    setActiveCategory($(element));

    $("#psthemecusto .js-wireframe div[class*='js-img-']").hover(
        function() {
            let name = $(this).data('module_name');
            $('.module-list [data-module_name='+ name +']').addClass('active');
            $(this).find('.active-element').addClass('hide');
            $(this).find('.out-element').addClass('hide');
            $(this).find('.hover-element').removeClass('hide');
        }, function() {
            let name = $(this).data('module_name');

            if (!$('.module-list [data-module_name='+ name +']').parent().hasClass('active')) {
                $('.module-list [data-module_name='+ name +']').removeClass('active');
                $(this).find('.hover-element').addClass('hide');
                $(this).find('.active-element').addClass('hide');
                $(this).find('.out-element').removeClass('hide');
            } else {
                $('.module-list [data-module_name='+ name +']').addClass('active');
                $(this).find('.hover-element').addClass('hide');
                $(this).find('.active-element').removeClass('hide');
                $(this).find('.out-element').addClass('hide');
            }
        }
    );

    $("#psthemecusto .module-list div.configuration-rectangle").hover(
        function() {
            let name = $(this).find('.js-module-name').data('module_name');
            let $rightSideElement = $('.js-wireframe [data-module_name='+ name +']');
            $rightSideElement.find('.active-element').removeClass('hide');
            $rightSideElement.find('.out-element').addClass('hide');
        }, function() {
            let name = $(this).find('.js-module-name').data('module_name');
            if (!$(this).hasClass('active')) {
                let $rightSideElement = $('.js-wireframe [data-module_name='+ name +']');
                $rightSideElement.find('.active-element').addClass('hide');
                $rightSideElement.find('.out-element').removeClass('hide');
            }
        }
    );

});
