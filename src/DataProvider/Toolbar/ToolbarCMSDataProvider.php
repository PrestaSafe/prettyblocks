<?php

namespace PrestaSafe\PrettyBlocks\DataProvider\Toolbar;

use Context;
use Exception;
use PrestaSafe\PrettyBlocks\Contract\Toolbar\ToolbarDataProviderInterface;

final class ToolbarCMSDataProvider implements ToolbarDataProviderInterface
{
    /**
     * @param string $term
     * @return array
     */
	public static function getByTerms(string $term): array
	{
        $context = Context::getContext();

        try {
            $cms = \Db::getInstance()->executeS('
                SELECT p.id_cms as id, pl.meta_title as text, p.id_cms_category
                FROM `ps_cms` p
                LEFT JOIN `ps_cms_lang` pl ON (p.id_cms = pl.id_cms)
                LEFT JOIN `ps_cms_shop` ps ON (p.id_cms = ps.id_cms)
                WHERE p.active = 1
                AND pl.id_lang = ' . $context->language->id . '
                AND pl.meta_title LIKE "%' . pSQL($term) . '%"' . '
                AND ps.id_shop = ' . $context->shop->id .'
                GROUP BY p.id_cms'
            );

            return self::formatData($cms);

        } catch (Exception $e) {
            return [];
        }
	}

    /**
     * @param array $data
     * @return array
     */
    public static function formatData(array $data): array
    {
        $link = Context::getContext()->link;

        foreach ($data as &$cms) {

            if (isset($cms['id'])) {
                $cms['img']   = "";
                $cms['link']  = $link->getCMSLink($cms['id']).'?prettyblock_preview';
            }
        }

        return $data;
    }
}
