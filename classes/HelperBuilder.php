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
class HelperBuilder
{
    /**
     * ex: $path $path = '$/modules/prettyblocks/views/images/'
     * return /path/to/prestashop/modules/prettyblocks/views/images/
     * @param String $path:  begin by $/
     * @param Bool trim
     * @return String
     */
    public static function pathFormatterFromString($path, $rtrim = false)
    {
        if (substr($path, 0, 1) !== '$') {
            throw new Exception('Path "' . $path . '" should begin by $ ex: "$/prettyblocks/path/to/images/"');
        }
        $pathFormatted = str_replace('$', _PS_ROOT_DIR_, $path);
        $path = realpath($pathFormatted) . '/';
        if ($rtrim) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * @todo Security Check
     */
    public static function pathFormatterFromUrl($path, $path_only = false)
    {
        $shop_domain = Tools::getShopDomainSsl(true);
        $pathFormatted = str_replace($shop_domain, _PS_ROOT_DIR_, $path);
        $file_name = pathinfo($pathFormatted, PATHINFO_BASENAME);
        if ($path_only) {
            return realpath(dirname($pathFormatted));
        }

        return realpath(dirname($pathFormatted)) . '/' . $file_name;
    }

    public static function hookToArray($hookName, $params = [])
    {
        $extraContent = Hook::exec($hookName, $params, null, true);
        $res = [];
        if (is_array($extraContent)) {
            foreach ($extraContent as $formField) {
                if (!is_array($formField)) {
                    continue;
                }
                foreach ($formField as $array) {
                    $res[] = $array;
                }
            }
        }

        return $res;
    }


    /**
     * * ex: $path = '$/modules/prettyblocks/views/images/'
     * return https://{prestashop_url}/modules/prettyblocks/views/images/
     * @param String $path:  begin by $/
     * @return String
     */
    public static function pathFormattedToUrl($path)
    {
        $path = self::pathFormatterFromString($path, true);
        $domain = Tools::getShopDomainSsl(true);

        $context = Context::getContext();
        $domain .= rtrim($context->shop->physical_uri,'/');
        return rtrim(str_replace(_PS_ROOT_DIR_, $domain , $path),'/');
    } 

}
