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

class ElementModel extends ObjectModel
{
    /**
     * @var int Identifiant unique de l'élément
     */
    public $id_element;
    
    /**
     * @var string Type d'élément (code de l'élément)
     */
    public $element_type;
    
    /**
     * @var string Identifiant unique généré pour l'élément
     */
    public $element_id;
    
    /**
     * @var string Identifiant de l'élément parent
     */
    public $parent_id;
    
    /**
     * @var int Identifiant du bloc PrettyBlocks associé
     */
    public $id_prettyblocks;
    
    /**
     * @var int Position de l'élément
     */
    public $position;
    
    /**
     * @var string Valeurs de l'élément (JSON)
     */
    public $values;
    
    /**
     * @var int Identifiant de la boutique
     */
    public $id_shop;
    
    /**
     * @var int Identifiant de la langue
     */
    public $id_lang;
    
    /**
     * @var string Date de création
     */
    public $date_add;
    
    /**
     * @var string Date de modification
     */
    public $date_upd;
    
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'prettyblocks_elements',
        'primary' => 'id_element',
        'fields' => [
            'element_type' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'element_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'parent_id' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'id_prettyblocks' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'default' => 0],
            'values' => ['type' => self::TYPE_STRING, 'validate' => 'isJson'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_lang' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
    
    /**
     * Récupère les éléments racines d'un bloc
     * 
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int|null $id_lang ID de la langue
     * @param int|null $id_shop ID de la boutique
     * @return array Éléments racines
     */
    public static function getRootElements($id_prettyblocks, $id_lang = null, $id_shop = null)
    {
        $id_lang = $id_lang ?: Context::getContext()->language->id;
        $id_shop = $id_shop ?: Context::getContext()->shop->id;
        
        $query = new DbQuery();
        $query->select('*')
              ->from(self::$definition['table'])
              ->where('id_prettyblocks = ' . (int) $id_prettyblocks)
              ->where('id_lang = ' . (int) $id_lang)
              ->where('id_shop = ' . (int) $id_shop)
              ->where('parent_id IS NULL OR parent_id = ""')
              ->orderBy('position ASC');
        
        $results = Db::getInstance()->executeS($query);
        $elements = [];
        
        if ($results) {
            foreach ($results as $row) {
                $element = new ElementModel();
                foreach ($row as $key => $value) {
                    $element->{$key} = $value;
                }
                $elements[] = $element;
            }
        }
        
        return $elements;
    }
    
    /**
     * Récupère les éléments enfants d'un élément parent
     * 
     * @param string $parent_id ID de l'élément parent
     * @param int|null $id_prettyblocks ID du bloc PrettyBlocks
     * @param int|null $id_lang ID de la langue
     * @param int|null $id_shop ID de la boutique
     * @return array Éléments enfants
     */
    public static function getChildElements($parent_id, $id_prettyblocks = null, $id_lang = null, $id_shop = null)
    {
        $id_lang = $id_lang ?: Context::getContext()->language->id;
        $id_shop = $id_shop ?: Context::getContext()->shop->id;
        
        $query = new DbQuery();
        $query->select('*')
              ->from(self::$definition['table'])
              ->where('parent_id = "' . pSQL($parent_id) . '"')
              ->where('id_lang = ' . (int) $id_lang)
              ->where('id_shop = ' . (int) $id_shop)
              ->orderBy('position ASC');
        
        if ($id_prettyblocks) {
            $query->where('id_prettyblocks = ' . (int) $id_prettyblocks);
        }
        
        $results = Db::getInstance()->executeS($query);
        $elements = [];
        
        if ($results) {
            foreach ($results as $row) {
                $element = new ElementModel();
                foreach ($row as $key => $value) {
                    $element->{$key} = $value;
                }
                $elements[] = $element;
            }
        }
        
        return $elements;
    }
    
    /**
     * Supprime tous les éléments d'un bloc
     * 
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int|null $id_lang ID de la langue
     * @param int|null $id_shop ID de la boutique
     * @return bool Succès de la suppression
     */
    public static function deleteElementsByBlockId($id_prettyblocks, $id_lang = null, $id_shop = null)
    {
        $id_lang = $id_lang ?: Context::getContext()->language->id;
        $id_shop = $id_shop ?: Context::getContext()->shop->id;
        
        return Db::getInstance()->execute('
            DELETE FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
            WHERE `id_prettyblocks` = ' . (int) $id_prettyblocks . '
            AND `id_lang` = ' . (int) $id_lang . '
            AND `id_shop` = ' . (int) $id_shop
        );
    }
    
    /**
     * Récupère et décode les valeurs
     * 
     * @return array Valeurs décodées
     */
    public function getDecodedValues()
    {
        if (empty($this->values)) {
            return [];
        }
        
        $values = json_decode($this->values, true);
        return is_array($values) ? $values : [];
    }
    
    /**
     * Définit et encode les valeurs
     * 
     * @param array $values Les valeurs à encoder
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = json_encode($values);
        return $this;
    }
    
    /**
     * Récupère tous les éléments d'un bloc
     * 
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int|null $id_lang ID de la langue
     * @param int|null $id_shop ID de la boutique
     * @return array Tous les éléments du bloc
     */
    public static function getAllElementsByBlockId($id_prettyblocks, $id_lang = null, $id_shop = null)
    {
        $id_lang = $id_lang ?: Context::getContext()->language->id;
        $id_shop = $id_shop ?: Context::getContext()->shop->id;
        
        $query = new DbQuery();
        $query->select('*')
              ->from(self::$definition['table'])
              ->where('id_prettyblocks = ' . (int) $id_prettyblocks)
              ->where('id_lang = ' . (int) $id_lang)
              ->where('id_shop = ' . (int) $id_shop)
              ->orderBy('position ASC');
        
        $results = Db::getInstance()->executeS($query);
        $elements = [];
        
        if ($results) {
            foreach ($results as $row) {
                $element = new ElementModel();
                foreach ($row as $key => $value) {
                    $element->{$key} = $value;
                }
                $elements[$element->element_id] = $element;
            }
        }
        
        return $elements;
    }
}