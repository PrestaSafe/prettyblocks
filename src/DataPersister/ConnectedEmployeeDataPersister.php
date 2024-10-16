<?php
namespace PrestaSafe\PrettyBlocks\DataPersister;

use \Db;
use Exception;

final class ConnectedEmployeeDataPersister
{
    /**
     * Delete old employee and insert new
     *
     * @param int    $id_user
     * @param string $session_id
     * @return bool
     */
    public static function insert(int $id_user, string $session_id): bool
    {
        try {

            // Delete old employee (more than 1 min)
            ConnectedEmployeeDataPersister::cleanData();

            return Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'prettyblocks_connected_employee` (id_user, session_id, last_update)
                VALUES (' .
                (int) pSQL($id_user) . ', "' .
                pSQL($session_id) . '", "' .
                date('Y-m-d H:i:s') .
                '")
                ON DUPLICATE KEY UPDATE last_update = "' . date('Y-m-d H:i:s') . '"'
            );
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * Update the date of the connected employee
     *
     * @param string $session_id
     * @return bool
     */
    public static function update(string $session_id): bool
    {
        try {

            return Db::getInstance()->update(
                'prettyblocks_connected_employee',
                ['last_update' => date('Y-m-d H:i:s')],
                'session_id = \'' . pSQL($session_id) . '\''
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete old employee (more than 1 min)
     *
     * @return bool
     */
    public static function cleanData(): bool
    {
        try {
            $database      = Db::getInstance();
            $thresholdTime = date('Y-m-d H:i:s', strtotime('-1 minute'));

            $sql = '
                DELETE FROM ' . _DB_PREFIX_ . 'prettyblocks_connected_employee 
                WHERE last_update < "' . pSQL($thresholdTime) . '"'
            ;

            return $database->execute($sql);
        } catch (Exception $e) {
            return false;
        }
    }
}