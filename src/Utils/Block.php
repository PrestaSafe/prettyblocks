<?php
/**
 * Copyright (c) Since 2020 PrestaSafe and contributors
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
 * @copyright Since 2020 PrestaSafe and contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaSafe
 */

namespace PrestaSafe\PrettyBlocks\Utils;

class Block
{
    public $fields;
    public $state = [];
    public $need_reload = false;
    public $inputs = [];

    public function registerFields($fields)
    {
    }

    public function setInput(array $array)
    {
        $this->inputs[] = $array;
    }

    public function getField($name)
    {
    }

    public function getCollection($field_name)
    {
    }

    public function getState()
    {
        return $this->state;
    }

    // $blocks[] =  [
    //     'name' => $this->l('CartZilla Slider'),
    //     'description' => 'Metus potenti velit sollicitudin porttitor magnis elit lacinia tempor varius, ut cras orci vitae parturient id nisi vulputate consectetur, primis venenatis cursus tristique malesuada viverra congue risus.',
    //     'code' => 'banner-products-2',
    //     'tab' => 'general',
    //     'icon' => 'PhotoIcon',
    //     'need_reload' => false,
    //     'templates' => [
    //         'module:'.$this->name.'/views/templates/blocks/banner-products.tpl'
    //     ],
    //     'section' => [
    //         'config' => [
    //             'category' => [
    //                 'type' => 'selector',
    //                 'label' => 'Category',
    //                 'collection' => 'Category',
    //                 'path' => '$/modules/prettyblocks/views/images/',
    //                 'default' => 'default value',
    //                 'selector' => '{id} - {name}'
    //             ],
    //             'product_num' => [
    //                 'type' => 'integer',
    //                 'default' => 12
    //             ]
    //         ],
    //     ],

    //     'repeater' => [
    //         'name' => 'Element repeated',
    //         'nameFrom' => 'title',
    //         'groups' => [
    //             'title' => [
    //                 'type' => 'text',
    //                 'label' => 'Custom title',
    //                 'default' => 'default value',
    //             ],
    //             'category' => [
    //                 'type' => 'selector',
    //                 'label' => 'Category',
    //                 'collection' => 'Category',
    //                 'path' => '$/modules/prettyblocks/views/images/',
    //                 'default' => 'default value',
    //                 'selector' => '{id} - {name}'
    //             ],
    //             'product' => [
    //                 'type' => 'selector',
    //                 'label' => 'Product',
    //                 'collection' => 'Product',
    //                 'path' => '$/modules/prettyblocks/views/images/',
    //                 'default' => 'default value',
    //                 'selector' => '{id} - {name}'
    //             ],
    //             'upload' => [
    //                 'type' => 'fileupload',
    //                 'label' => 'File upload',
    //                 'path' => '$/modules/prettyblocks/views/images/',
    //                 'default' => [
    //                     'imgs' => [
    //                         ['url' => 'https://via.placeholder.com/141x180'],
    //                     ]
    //                 ],
    //             ]
    //         ],
    //     ],

    // ];
}
