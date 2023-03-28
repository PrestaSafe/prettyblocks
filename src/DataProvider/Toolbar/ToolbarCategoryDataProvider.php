<?php

namespace PrestaSafe\PrettyBlocks\DataProvider\Toolbar;

use Context;
use Exception;
use PrestaSafe\PrettyBlocks\Contract\Toolbar\ToolbarDataProviderInterface;

final class ToolbarCategoryDataProvider implements ToolbarDataProviderInterface
{
    /**
     * @param string $term
     * @return array
     */
	public static function getByTerms(string $term): array
	{
        $context = Context::getContext();

        try {
            $categories = \Db::getInstance()->executeS('
                SELECT p.id_category as id, pl.name as text, pl.link_rewrite
                FROM `ps_category` p
                LEFT JOIN `ps_category_lang` pl ON (p.id_category = pl.id_category)
                LEFT JOIN `ps_category_shop` ps ON (p.id_category = ps.id_category)
                WHERE p.active = 1
                AND pl.id_lang = ' . $context->language->id . '
                AND pl.name LIKE "%' . pSQL($term) . '%"' . '
                AND ps.id_shop = ' . $context->shop->id .'
                GROUP BY p.id_category'
            );

            return self::formatData($categories);

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

        foreach ($data as &$category) {

            // Image
            if (isset($category['id'])) {
                $category['img']   = $link->getCatImageLink($category['link_rewrite'] ?? "", $category['id'] ?? 0, 'small_default');
                $category['link']  = $link->getCategoryLink($category['id']);
            }
        }

        return $data;
    }
}
