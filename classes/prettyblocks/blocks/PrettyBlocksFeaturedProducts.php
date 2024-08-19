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

class PrettyBlocksFeaturedProducts implements BlockInterface
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function registerBlocks(): array
    {
        return [
            'name' => $this->module->l('PrettyBlocks Featured products blocks'),
            'description' => $this->module->l('Render featured block'),
            'code' => 'prettyblocks_featured_product',
            'tab' => 'general',
            'icon' => 'GiftIcon',
            'need_reload' => true,
            'templates' => [
                'default' => 'module:' . $this->module->name . '/views/templates/blocks/featured_products/default.tpl',
            ],
            'config' => [
                'fields' => [
                    'category' => [
                        'type' => 'selector',
                        'label' => 'Category',
                        'collection' => 'Category',
                        'default' => HelperBuilder::getRandomCategory(Context::getContext()->language->id, Context::getContext()->shop->id),
                        'selector' => '{id} - {name}',
                    ],
                    'number' => [
                        'type' => 'text',
                        'label' => $this->module->l('Number of products'),
                        'default' => 8,
                    ],
                    'title' => [
                        'type' => 'text',
                        'default' => $this->module->l('Our products'),
                        'label' => 'Title to display',
                    ],
                    'display_title' => [
                        'type' => 'checkbox',
                        'default' => true,
                        'label' => $this->module->l('Display title of block'),
                    ],
                    'display_link' => [
                        'type' => 'checkbox',
                        'default' => true,
                        'label' => $this->module->l('Display links to category'),
                    ],
                ],
            ],
        ];
    }
}
