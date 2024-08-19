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

class PrettyBlocksFAQ implements BlockInterface
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function registerBlocks(): array
    {
        return [
            'name' => $this->module->l('PrettyBlocks FAQ Block'),
            'description' => $this->module->l('Render a simple faq'),
            'code' => 'prettyblocks_faq',
            'tab' => 'sliders',
            'icon' => 'RectangleStackIcon',
            'need_reload' => false,
            'insert_default_values' => true,
            'templates' => [
                'default' => 'module:' . $this->module->name . '/views/templates/blocks/faq/default.tpl',
            ],
            'config' => [
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => $this->module->l('Title'),
                        'default' => 'FAQ',
                    ],
                ],
            ],

            'repeater' => [
                'name' => 'Q&A',
                'nameFrom' => 'question',
                'groups' => [
                    'question' => [
                        'type' => 'text',
                        'label' => $this->module->l('Question'),
                        'default' => 'Question',
                    ],
                    'answer' => [
                        'type' => 'editor',
                        'label' => $this->module->l('Answer'),
                        'default' => '<p>Answer</p>',
                    ],
                ],
            ],
        ];
    }
}
