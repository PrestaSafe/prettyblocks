<?php

namespace PrestaSafe\PrettyBlocks\DataProvider;

final class ConnectedEmployeeDataProvider
{
    /**
     * Return the number of connected employees on the page
     *
     * @return int|null
     */
    public static function get(): ?int
    {
        try {
            $sql = '
                SELECT COUNT(*) 
                FROM `' . _DB_PREFIX_ . 'prettyblocks_connected_employee` 
                WHERE last_update > DATE_SUB(NOW(), INTERVAL 60 SECOND)
            ';

            return (int) \Db::getInstance()->getValue($sql);
        } catch (\Exception $e) {
            return null;
        }
    }
}
