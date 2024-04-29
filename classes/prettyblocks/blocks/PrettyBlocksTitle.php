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

class PrettyBlocksTitle implements BlockInterface
{
	private $module;

	public function __construct($module)
	{
		$this->module = $module;
	}

	public function registerBlocks(): array
	{
		return [
			'name' => $this->module->l('PrettyBlocks Title blocks'),
			'description' => $this->module->l('Render title block'),
			'code' => 'prettyblocks_title',
			'tab' => 'general',
			'icon' => 'DocumentTextIcon',
			'need_reload' => true,
			'templates' => [
				'default' => 'module:' . $this->module->name . '/views/templates/blocks/title/default.tpl'
			],
			'config' => [
				'fields' => [
					'tag' => [
						'type' => 'select', // type of field
						'label' => $this->module->l('Choose a tag'), // label to display
						'default' => 'h2', // default value (String)
						'choices' => [
							'h1' => 'h1',
							'h2' => 'h2',
							'h3' => 'h3',
							'h4' => 'h4',
							'h5' => 'h5',
							'h6' => 'h6',
						]
					],
					'title' => [
						'type' => 'text',
						'default' => $this->module->l('Your title'),
						'label' => $this->module->l('You can use HTML tags to customize your title.')
					],
					'classes' => [
						'type' => 'text',
						'default' => '',
						'label' => $this->module->l('Add classes to your title')
					],
				],
			],
		];
	}
}
