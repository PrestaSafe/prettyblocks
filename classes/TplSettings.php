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
class TplSettings
{
    public static function getSettings($key, $default = '')
    {
        $smarty_vars = self::getVars();
        $result = (isset($smarty_vars['prettyblocks']['theme_settings'][$key]))
            ? $smarty_vars['prettyblocks']['theme_settings'][$key]
            : $default;

        return $result;
    }

    public static function getVars()
    {
        $cache_id = 'PrettyBlocks::getVars';
        if (!Cache::isStored($cache_id)) {
            // $smarty_vars = Context::getContext()->smarty->getTemplateVars();
            $smarty_vars = Hook::exec('ActionFrontControllerSetVariables', [], null, true);
            // dump($smarty_vars);
            Cache::store($cache_id, $smarty_vars);
        }

        return Cache::retrieve($cache_id);
    }
}
