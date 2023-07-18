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
/*
 * Please don't judge me
 * it will be refactoring soon.
 */
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;

class StateFormatter
{
    public static function formatFieldUpload($value)
    {
        $url = $value['value']['url'] ?: '';
        $extension = '';
        $mediatype = '';
        $filename = '';
        $size = 0;
        $width = 0;
        $height = 0;

        if (!empty($url)) {
            // add extension
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            // add media type (image, document, video, ...)
            $mediatype = HelperBuilder::getMediaTypeForExtension($extension);
            // add filename
            $filename = pathinfo($url, PATHINFO_BASENAME);

            $path = HelperBuilder::pathFormattedFromUrl($url);

            if (file_exists($path)) {
                $size = filesize($path);

                // if file is an image, we return width and height
                if ($mediatype == 'image') {
                    list($width, $height) = getimagesize($path);
                }
            }
        }

        return $value['value'] = [
            'url' => $url,
            'extension' => $extension,
            'mediatype' => $mediatype,
            'filename' => $filename,
            'size' => (int) $size,
            'width' => (int) $width,
            'height' => (int) $height,
        ];
    }

    public static function formatFieldSelector($value)
    {
        if ($value['collection']) {
            $json = $value['value'];
            if (!isset($json['show']['id'])) {
                return false;
            }
            // @TODO -> Presenter
            $c = new PrestaShopCollection($value['collection'], Context::getContext()->language->id);
            $primary = $value['primary'] ?? 'id_' . Tools::strtolower($value['collection']);
            $object = $c->where($primary, '=', (int) $json['show']['id'])->getFirst();

            if (!Validate::isLoadedObject($object)) {
                return false;
            }

            $objectPresenter = new ObjectPresenter();

            return $objectPresenter->present($object);
        }

        return false;
    }

    public static function formatFieldDefault($value)
    {
        return $value['value'] ?? '';
    }
}
