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

use PrestaSafe\PrettyBlocks\Interfaces\BlockInterface;

class PrettyBlocksTinySlider implements BlockInterface
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function registerBlocks(): array
    {
        return [
            'name' => $this->module->l('PrettyBlocks Tiny Slider'),
            'description' => $this->module->l('Render a simple and nice slider'),
            'code' => 'prettyblocks_tiny_slider',
            'tab' => 'sliders',
            'icon' => 'RectangleStackIcon',
            'need_reload' => true,
            'insert_default_values' => true,
            'templates' => [
                'default' => 'module:' . $this->module->name . '/views/templates/blocks/tinyslider/default.tpl',
            ],

            'repeater' => [
                'name' => 'Slides',
                'nameFrom' => 'alt_image',
                'groups' => [
                    'image' => [
                        'type' => 'fileupload',
                        'label' => $this->module->l('File upload'),
                        'path' => '$/modules/' . $this->module->name . '/views/images/',
                        'default' => [
                            'url' => 'https://placehold.co/1110x522',
                        ],
                    ],
                    'alt_image' => [
                        'type' => 'text',
                        'label' => $this->module->l('Image Alt'),
                        'default' => 'Image alt',
                    ],
                ],
            ],
        ];
    }
}
