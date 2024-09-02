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
use PrestaShop\PrestaShop\Adapter\Presenter\PresenterInterface;

class BlockPresenter implements PresenterInterface
{
    public function present($block)
    {
        $present = [
            'settings' => $block['settings'],
            'settings_formatted' => !empty($block['settings_formatted']) ? $block['settings_formatted'] : [],
            'states' => $block['states'],
            'extra' => $block['extra'],
            'instance_id' => $block['instance_id'],
            'id_shop' => $block['id_shop'],
            'id_prettyblocks' => $block['id_prettyblocks'],
            'templateSelected' => $block['templateSelected'],
            'templates' => $block['templates'],
            'classes' => $block['classes'],
            'styles' => $block['styles'],
        ];

        return $present;
    }
}
