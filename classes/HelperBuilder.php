<?php

use PrestaSafe\PrettyBlocks\Interfaces\BlockInterface;

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
class HelperBuilder
{
    /**
     * ex: $path $path = '$/modules/prettyblocks/views/images/'
     * return /path/to/prestashop/modules/prettyblocks/views/images/
     *
     * @param string $path: begin by $/
     * @param bool trim
     *
     * @return string
     */
    public static function pathFormattedFromString($path, $rtrim = false)
    {
        if (strpos($path, '..') !== false) {
            throw new Exception('Invalid path');
        }
        if ($path[0] !== '$') {
            throw new Exception('Path "' . $path . '" should begin by $ ex: "$/prettyblocks/path/to/images/"');
        }
        $pathFormatted = str_replace('$', _PS_ROOT_DIR_, $path);

        if (substr($pathFormatted, -1) === '/') {
            $pathFormatted = substr($pathFormatted, 0, -1);
        }
        $path = realpath($pathFormatted) . '/';
        if ($rtrim) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * ex: $path = 'https://prestashop_url/modules/prettyblocks/views/images/'
     * return /path/to/prestashop/modules/prettyblocks/views/images/
     *
     * @param string $path
     * @param bool $path_only
     *
     * @return string
     */
    public static function pathFormattedFromUrl($path, $path_only = false)
    {
        $shop_domain = Tools::getShopDomainSsl(true);
        $pathFormatted = str_replace($shop_domain, _PS_ROOT_DIR_, $path);
        $file_name = pathinfo($pathFormatted, PATHINFO_BASENAME);
        if ($path_only) {
            return realpath(dirname($pathFormatted));
        }

        return realpath(dirname($pathFormatted)) . '/' . $file_name;
    }

    /**
     * Retourne hook data module to Array
     *
     * @param string $hookName
     * @param array $params
     *
     * @return array
     */
    public static function hookToArray($hookName, $params = [])
    {
        $extraContent = Hook::exec($hookName, $params, null, true);
        $res = [];
        if (is_array($extraContent)) {
            foreach ($extraContent as $formField) {
                if (!is_array($formField)) {
                    continue;
                }
                foreach ($formField as $key => $array) {
                    $res[$key] = $array;
                }
            }
        }

        return $res;
    }

    /**
     * * ex: $path = '$/modules/prettyblocks/views/images/'
     * return https://{prestashop_url}/modules/prettyblocks/views/images/
     *
     * @param string $path: begin by $/
     *
     * @return string
     */
    public static function pathFormattedToUrl($path)
    {
        $path = self::pathFormattedFromString($path, true);
        $domain = Tools::getShopDomainSsl(true);

        $context = Context::getContext();
        $domain .= rtrim($context->shop->physical_uri, '/');

        return rtrim(str_replace(_PS_ROOT_DIR_, $domain, $path), '/');
    }

    /**
     * Return array of blocks
     *
     * @param $blocks
     *
     * @return array
     */
    public static function renderBlocks($blocks)
    {
        $output = [];
        foreach ($blocks as $block) {
            if ($block instanceof BlockInterface) {
                $output[] = $block->registerBlocks();
            }
        }

        return $output;
    }

    /**
     * return random category formatted for default collection field
     *
     * @param int $id_lang
     * @param int $id_shop
     *
     * @return array
     */
    public static function getRandomCategory($id_lang = null, $id_shop = null)
    {
        $id_lang = ($id_lang ? $id_lang : Context::getContext()->language->id);
        $id_shop = ($id_shop ? $id_shop : Context::getContext()->shop->id);

        $ids = Db::getInstance()->executeS('SELECT l.id_category FROM ' . _DB_PREFIX_ . 'category_lang as l
        INNER JOIN ' . _DB_PREFIX_ . 'category c ON (c.id_category = l.id_category)
        WHERE c.active = 1
        AND l.id_shop = ' . (int) $id_shop . ' AND l.id_lang = ' . (int) $id_lang . '
        ORDER BY RAND() LIMIT 5');
        $categoriesIDS = array_map(function ($element) {
            return $element['id_category'];
        }, $ids);

        $randomIndex = array_rand($categoriesIDS);
        $id_category = $categoriesIDS[$randomIndex];

        $category = (new PrestaShopCollection('Category', $id_lang))
            ->where('id_category', '=', $id_category)->getFirst();
        $secure = [];
        $secure['show'] = [
            'id' => (int) $id_category,
            'primary' => (int) $id_category,
            'name' => $category->name,
            'formatted' => $id_category . ' - ' . $category->name,
        ];

        return $secure;
    }

    /**
     * return random product formatted for default collection field
     *
     * @param string $collectionName
     * @param int $id_lang
     * @param int $id_shop
     *
     * @return array
     */
    public static function getRandomProduct($id_lang = null, $id_shop = null)
    {
        $collectionName = 'Product';
        $id_lang = ($id_lang ? $id_lang : Context::getContext()->language->id);
        $id_shop = ($id_shop ? $id_shop : Context::getContext()->shop->id);
        $collection = strtolower($collectionName);

        $sql = 'SELECT l.id_' . $collection . ' FROM ' . _DB_PREFIX_ . $collection . '_lang as l
        INNER JOIN ' . _DB_PREFIX_ . $collection . ' c ON (c.id_' . $collection . ' = l.id_' . $collection . ')
        WHERE c.active = 1
        AND l.id_shop = ' . (int) $id_shop . ' AND l.id_lang = ' . (int) $id_lang . '
        ORDER BY RAND() LIMIT 5';
        $ids = Db::getInstance()->executeS($sql);
        $collectionIDS = array_map(function ($element) use ($collection) {
            return $element['id_' . $collection];
        }, $ids);

        if (empty($collectionIDS)) {
            $array = [];
            $array['show'] = [
                'id' => 0,
                'primary' => 0,
                'name' => 'no results found',
                'formatted' => 0 . ' - no results found',
            ];

            return $array;
        }
        $randomIndex = array_rand($collectionIDS);
        $id_collection = $collectionIDS[$randomIndex];

        $model = (new PrestaShopCollection($collectionName, $id_lang))
            ->where('id_' . $collection, '=', $id_collection)->getFirst();
        $secure = [];
        $secure['show'] = [
            'id' => (int) $id_collection,
            'primary' => (int) $id_collection,
            'name' => $model->name,
            'formatted' => $id_collection . ' - ' . $model->name,
        ];

        return $secure;
    }

    /**
     * return random product formatted for default collection field
     *
     * @param string $collectionName
     * @param int $id_lang
     * @param int $id_shop
     *
     * @return array
     */
    public static function getRandomCMS($id_lang = null, $id_shop = null)
    {
        $collectionName = 'CMS';
        $id_lang = ($id_lang ? $id_lang : Context::getContext()->language->id);
        $id_shop = ($id_shop ? $id_shop : Context::getContext()->shop->id);
        $collection = strtolower($collectionName);

        $sql = 'SELECT l.id_' . $collection . ' FROM ' . _DB_PREFIX_ . $collection . '_lang as l
        INNER JOIN ' . _DB_PREFIX_ . $collection . ' c ON (c.id_' . $collection . ' = l.id_' . $collection . ')
        WHERE c.active = 1
        AND l.id_shop = ' . (int) $id_shop . ' AND l.id_lang = ' . (int) $id_lang . '
        ORDER BY RAND() LIMIT 5';
        $ids = Db::getInstance()->executeS($sql);
        $collectionIDS = array_map(function ($element) use ($collection) {
            return $element['id_' . $collection];
        }, $ids);

        $randomIndex = array_rand($collectionIDS);
        $id_collection = $collectionIDS[$randomIndex];

        $model = (new PrestaShopCollection($collectionName, $id_lang))
            ->where('id_' . $collection, '=', $id_collection)->getFirst();
        $secure = [];
        $secure['show'] = [
            'id' => (int) $id_collection,
            'primary' => (int) $id_collection,
            'name' => $model->meta_title,
            'formatted' => $id_collection . ' - ' . $model->meta_title,
        ];

        return $secure;
    }

    /**
     * The function determines the media type (image, document, video, etc.) based on the file
     * extension provided.
     *
     * @param string extension The parameter "extension" is a string that represents the file extension of a
     * file. For example, if the file is named "image.jpg", the extension would be "jpg".
     *
     * @return string a string that represents the media type
     */
    public static function getMediaTypeForExtension($extension): string
    {
        // media type (image, document, video, ...)
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
            return 'image';
        }
        if (in_array($extension, ['mov', 'mp4', 'webm', 'ogg', 'ogv'])) {
            return 'image';
        }

        return 'document';
    }
}
