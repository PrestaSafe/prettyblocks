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

class CustomImage implements BlockInterface
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function registerBlocks(): array
    {
        $context = Context::getContext();

        return [
            'name' => $this->module->l('PrettyBlocks Custom Image Block'),
            'description' => $this->module->l('Display custom text anywhere'),
            'code' => 'custom_image_block', // unique code
            'tab' => 'general', // for future use
            'icon' => 'DocumentTextIcon', // heroicons v2
            'insert_default_values' => true, // insert default values on new block
            'need_reload' => false, // reload iframe on save
            'templates' => [
                'default' => 'module:' . $this->module->name . '/views/templates/blocks/custom_image.tpl',
            ],
            'config' => [
                'fields' => [
                    'image' => [
                        'type' => 'fileupload',
                        'force_default_value' => true,
                        'label' => $this->module->l('Image first banner'),
                        'path' => '$/modules/' . $this->module->name . '/views/images/',
                        'default' => [
                            'url' => 'https://placehold.co/600x400',
                        ],
                    ],

                    'alignment' => [
                        'type' => 'select',
                        'label' => $this->module->l('Choose alignment'),
                        'default' => 'left',
                        'choices' => [
                            'left' => 'left',
                            'center' => 'center',
                            'right' => 'right',
                        ],
                    ],
                ],
            ],
        ];
    }
}
