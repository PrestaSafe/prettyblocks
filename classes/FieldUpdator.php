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
/**
 * Please don't judge me
 * it will be refactoring soon.
 */
class FieldUpdator
{
    public static function updateFieldText($name, $value = false, $block = false, $suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        $id_shop = ($block && $block['id_shop']) ? (int) $block['id_shop'] : (int) Context::getContext()->shop->id;
        if ($value !== false) {
            Configuration::updateValue($key, $value, true, null, $id_shop);
        }
    }

    public static function updateFieldBoxes($name, $value = false, $block = false, $suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        $id_shop = ($block && $block['id_shop']) ? (int) $block['id_shop'] : (int) Context::getContext()->shop->id;
        if ($value !== false) {
            $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            if (!$bool) {
                $bool = 0;
            } else {
                $bool = 1;
            }

            return Configuration::updateValue($key, $bool, true, null, $id_shop);
        }
        Configuration::updateValue($key, 0, true, null, $id_shop);
    }

    public static function updateFieldUpload($name, $value = false, $block = false, $suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        $id_shop = ($block && $block['id_shop']) ? (int) $block['id_shop'] : (int) Context::getContext()->shop->id;
        if ($value !== false) {
            $format = $value;
            $to_json = json_encode($format);
            Configuration::updateValue($key, $to_json, true, null, $id_shop);
        }
    }

    public static function updateFieldSelector($name, $value = false, $block = false, $suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        if (!isset($value['show'])) {
            return false;
        }
        $collection = json_encode($value, true);
        $id_shop = ($block && $block['id_shop']) ? (int) $block['id_shop'] : (int) Context::getContext()->shop->id;
        Configuration::updateValue($key, $collection, true, null, $id_shop);
    }

    public static function updateFieldSelect($name, $value = false, $block = false, $suffix = '_config')
    {
        self::updateFieldText($name, $value, $block, $suffix);
    }

    public static function updateFieldRadioGroup($name, $value = false, $block = false, $suffix = '_config')
    {
        self::updateFieldText($name, $value, $block, $suffix);
    }

    private static function getKey($name, $block = false, $suffix = '_config')
    {
        if (!$block) {
            $key = Tools::strtoupper($name . $suffix);
        } else {
            $key = Tools::strtoupper($block['id_prettyblocks'] . '_' . $name . $suffix);
        }

        return $key;
    }

    public static function updateFieldEditor($name, $value = false, $block = false, $suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        $id_shop = ($block && $block['id_shop']) ? (int) $block['id_shop'] : (int) Context::getContext()->shop->id;
        if ($value !== false) {
            Configuration::updateValue($key, $value, true, null, $id_shop);
        }
    }
}
