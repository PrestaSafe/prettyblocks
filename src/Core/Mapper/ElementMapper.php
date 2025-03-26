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

namespace PrestaSafe\PrettyBlocks\Core\Mapper;

use PrestaSafe\PrettyBlocks\Core\ElementManager;
use ElementModel;

class ElementMapper
{
    /**
     * @var ElementManager Instance du gestionnaire d'éléments
     */
    protected $elementManager;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->elementManager = ElementManager::getInstance();
    }
    
    /**
     * Charge et construit la hiérarchie d'éléments pour un bloc
     * 
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int|null $id_lang ID de la langue
     * @param int|null $id_shop ID de la boutique
     * @return array Les éléments racines avec leurs enfants
     */
    public function loadElementsForBlock($id_prettyblocks, $id_lang = null, $id_shop = null)
    {
        $id_lang = $id_lang ?: \Context::getContext()->language->id;
        $id_shop = $id_shop ?: \Context::getContext()->shop->id;
        
        // Charger les éléments racines
        $rootElements = ElementModel::getRootElements($id_prettyblocks, $id_lang, $id_shop);
        
        // Construire les éléments
        $elements = [];
        
        foreach ($rootElements as $rootElement) {
            $element = $this->buildElementFromModel($rootElement, $id_prettyblocks, $id_lang, $id_shop);
            if ($element) {
                $elements[] = $element;
            }
        }
        
        return $elements;
    }
    
    /**
     * Construit un élément à partir d'un ElementModel
     * 
     * @param ElementModel $elementModel Le modèle d'élément
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int $id_lang ID de la langue
     * @param int $id_shop ID de la boutique
     * @return \PrestaSafe\PrettyBlocks\Core\Components\Element|null L'élément construit ou null
     */
    protected function buildElementFromModel($elementModel, $id_prettyblocks, $id_lang, $id_shop)
    {
        // Récupérer le prototype de l'élément
        $elementType = $elementModel->element_type;
        $element = $this->elementManager->createElement($elementType, $elementModel->element_id);
        
        if (!$element) {
            return null; // Type d'élément non trouvé
        }
        
        // Appliquer les valeurs de configuration
        $values = $elementModel->getDecodedValues();
        if (!empty($values)) {
            $element->setValues($values);
        }
        
        // Charger et ajouter les enfants
        $childModels = ElementModel::getChildElements($elementModel->element_id, $id_prettyblocks, $id_lang, $id_shop);
        
        foreach ($childModels as $childModel) {
            $childElement = $this->buildElementFromModel($childModel, $id_prettyblocks, $id_lang, $id_shop);
            if ($childElement) {
                $element->addElement($childElement);
            }
        }
        
        return $element;
    }
    
    /**
     * Sauvegarde une hiérarchie d'éléments pour un bloc
     * 
     * @param array $elements Les éléments à sauvegarder
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int|null $id_lang ID de la langue
     * @param int|null $id_shop ID de la boutique
     * @return bool Succès de l'opération
     */
    public function saveElementsForBlock($elements, $id_prettyblocks, $id_lang = null, $id_shop = null)
    {
        $id_lang = $id_lang ?: \Context::getContext()->language->id;
        $id_shop = $id_shop ?: \Context::getContext()->shop->id;
        
        // Supprimer les éléments existants pour ce bloc
        ElementModel::deleteElementsByBlockId($id_prettyblocks, $id_lang, $id_shop);
        
        // Sauvegarder les nouveaux éléments
        foreach ($elements as $position => $element) {
            $this->saveElement($element, null, $position, $id_prettyblocks, $id_lang, $id_shop);
        }
        
        return true;
    }
    
    /**
     * Sauvegarde récursivement un élément et ses enfants
     * 
     * @param \PrestaSafe\PrettyBlocks\Core\Components\Element $element L'élément à sauvegarder
     * @param string|null $parentId ID de l'élément parent
     * @param int $position Position de l'élément
     * @param int $id_prettyblocks ID du bloc PrettyBlocks
     * @param int $id_lang ID de la langue
     * @param int $id_shop ID de la boutique
     * @return bool Succès de l'opération
     */
    protected function saveElement($element, $parentId, $position, $id_prettyblocks, $id_lang, $id_shop)
    {
        // Créer un nouveau ElementModel
        $elementModel = new ElementModel();
        $elementModel->element_type = $element->getCode();
        $elementModel->element_id = $element->getId();
        $elementModel->parent_id = $parentId;
        $elementModel->id_prettyblocks = (int) $id_prettyblocks;
        $elementModel->position = (int) $position;
        $elementModel->setValues($element->getValues());
        $elementModel->id_shop = (int) $id_shop;
        $elementModel->id_lang = (int) $id_lang;
        $elementModel->date_add = date('Y-m-d H:i:s');
        $elementModel->date_upd = date('Y-m-d H:i:s');
        
        // Sauvegarder l'élément
        $result = $elementModel->save();
        
        if (!$result) {
            return false;
        }
        
        // Sauvegarder récursivement les enfants
        $children = $element->getElements();
        
        foreach ($children as $childPosition => $child) {
            $childResult = $this->saveElement(
                $child, 
                $element->getId(), 
                $childPosition, 
                $id_prettyblocks, 
                $id_lang, 
                $id_shop
            );
            
            if (!$childResult) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Convertit un ElementModel en tableau pour l'API
     * 
     * @param ElementModel $elementModel Le modèle d'élément
     * @return array Les données au format tableau
     */
    public function elementModelToArray($elementModel)
    {
        return [
            'id_element' => $elementModel->id_element,
            'element_type' => $elementModel->element_type,
            'element_id' => $elementModel->element_id,
            'parent_id' => $elementModel->parent_id,
            'id_prettyblocks' => $elementModel->id_prettyblocks,
            'position' => $elementModel->position,
            'values' => $elementModel->getDecodedValues(),
        ];
    }
    
    /**
     * Construit la hiérarchie complète des éléments à partir des modèles plats
     * 
     * @param array $elementModels Les modèles d'éléments
     * @return array La hiérarchie des éléments au format tableau
     */
    public function buildElementHierarchy($elementModels)
    {
        // Organiser par parent_id
        $elementsByParent = [];
        
        foreach ($elementModels as $elementId => $elementModel) {
            $parentId = $elementModel->parent_id ?: 'root';
            
            if (!isset($elementsByParent[$parentId])) {
                $elementsByParent[$parentId] = [];
            }
            
            $elementsByParent[$parentId][] = $elementModel;
        }
        
        // Fonction récursive pour construire l'arbre
        $buildTree = function ($parentId) use (&$buildTree, $elementsByParent) {
            if (!isset($elementsByParent[$parentId])) {
                return [];
            }
            
            $result = [];
            
            foreach ($elementsByParent[$parentId] as $elementModel) {
                $nodeData = $this->elementModelToArray($elementModel);
                $children = $buildTree($elementModel->element_id);
                
                if (!empty($children)) {
                    $nodeData['children'] = $children;
                }
                
                $result[] = $nodeData;
            }
            
            return $result;
        };
        
        // Construire l'arbre à partir de la racine
        return $buildTree('root');
    }
}