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

namespace PrestaSafe\PrettyBlocks\Presenter;

use PrestaShop\PrestaShop\Adapter\Presenter\PresenterInterface;

class FieldPresenter implements PresenterInterface
{
    public function present($field)
    {
        $present = [
            'tab' => $field->getFieldData('tab'),
            'type' => $field->getFieldData('type'),
            'default' => $field->getFieldData('default'),
            'label' => $field->getFieldData('label'),
            'force_default_value' => $field->getFieldData('force_default_value'),
            'path' => $field->getFieldData('path'),
            'collection' => $field->getFieldData('collection'),
            'selector' => $field->getFieldData('selector'),
            'choices' => $field->getFieldData('choices', []),
            'value' => $field->getValue(),
            'formatted_value' => $field->getFormattedValue(),
        ];

        return $present;
    }
}
