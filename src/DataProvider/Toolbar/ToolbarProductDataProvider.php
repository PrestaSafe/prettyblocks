<?php

namespace PrestaSafe\PrettyBlocks\DataProvider\Toolbar;

use Context;
use Exception;
use PrestaSafe\PrettyBlocks\Contract\Toolbar\ToolbarDataProviderInterface;
use PrestaShopException;
use Product;

final class ToolbarProductDataProvider implements ToolbarDataProviderInterface
{
    /**
     * @param string $term
     * @return array
     */
	public static function getByTerms(string $term): array
	{
        $context = Context::getContext();

        try {
            $products = \Db::getInstance()->executeS('
                SELECT p.reference as reference, p.id_product as id, pl.name as text, pl.link_rewrite
                FROM `ps_product` p
                LEFT JOIN `ps_product_lang` pl ON (p.id_product = pl.id_product)
                LEFT JOIN `ps_product_shop` ps ON (p.id_product = ps.id_product) 
                LEFT JOIN `ps_product_attribute` pa ON (p.id_product = pa.id_product) 
                LEFT JOIN `ps_stock_available` sa ON (sa.id_product = p.id_product) 
                WHERE p.active = 1
                AND p.reference != "" 
                AND pl.id_lang = ' . $context->language->id . '
                AND sa.quantity > 0
                AND (
                       (pl.name LIKE "%' . pSQL($term) . '%"' . ' OR
                       p.reference LIKE "%' . pSQL($term) . '%"' . ')
                       OR
                       (pl.name LIKE "%' . pSQL($term) . '%"' . ' OR
                       pa.reference LIKE "%' . pSQL($term) . '%"' . ')
                    )
                AND ps.id_shop = ' . $context->shop->id .'
                GROUP BY p.id_product'
            );

            return self::formatData($products);

        } catch (Exception $e) {
            return [];
        }
	}

    /**
     * @param array $data
     * @return array
     * @throws PrestaShopException
     */
    public static function formatData(array $data): array
    {
        $link = Context::getContext()->link;

        foreach ($data as &$product) {

            // Image
            if (isset($product['id'])) {
                $cover            = Product::getCover($product['id']);
                $product['img']   = $link->getImageLink($product['link_rewrite'], $cover ? $cover['id_image'] : '', 'small_default');
                $product['link']  = $link->getProductLink($product['id'], null, null, null, $product['id_product_attribute'] ?? 0);
            }
        }

        return $data;
    }
}
