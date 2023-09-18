<?php

use PrestaSafe\PrettyBlocks\Core\FieldCore;

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
class PrettyBlocksSettingsModel extends ObjectModel
{
    public $id_prettyblocks_settings;
    public $theme_name;
    public $profile;
    public $settings;
    public $id_shop;
    public $date_add;
    public $date_upd;
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'prettyblocks_settings',
        'primary' => 'id_prettyblocks_settings',
        'fields' => [
            'theme_name' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            'profile' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'settings' => ['type' => self::TYPE_SQL, 'validate' => 'isJson'],
        ],
    ];

    public static function getSettings($theme_name, $id_shop = null)
    {
        if ($id_shop === null) {
            $id_shop = Context::getContext()->shop->id;
        }
        $json = Db::getInstance()->getValue('SELECT settings FROM ' . _DB_PREFIX_ . 'prettyblocks_settings WHERE theme_name = "' . pSQL($theme_name) . '" AND id_shop = ' . (int) $id_shop);
        if (!$json) {
            return [];
        }
        $json = json_decode($json, true);
        foreach ($json as $key => $value) {
            $json[$key] = (new FieldCore($value))->compile();
        }

        return $json;
    }

    public static function getProfileByTheme($theme_name, $id_shop)
    {
        $collection = new PrestaShopCollection('PrettyBlocksSettingsModel');
        $collection->where('theme_name', '=', $theme_name);
        $collection->where('id_shop', '=', $id_shop);

        if (!$collection->getFirst()) {
            return self::generateFirstProfile($theme_name, $id_shop);
        }

        return $collection->getFirst();
    }

    public static function generateFirstProfile($theme_name = null, $id_shop = null)
    {
        if ($theme_name === null) {
            $theme_name = Context::getContext()->shop->theme_name;
        }
        if ($id_shop === null) {
            $id_shop = Context::getContext()->shop->id;
        }
        $prettyBlocksSettingsModel = new PrettyBlocksSettingsModel();
        $prettyBlocksSettingsModel->theme_name = $theme_name;
        $prettyBlocksSettingsModel->id_shop = $id_shop;
        $prettyBlocksSettingsModel->save();

        return $prettyBlocksSettingsModel;
    }
}
