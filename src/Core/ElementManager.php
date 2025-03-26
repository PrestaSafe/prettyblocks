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

namespace PrestaSafe\PrettyBlocks\Core;

use PrestaSafe\PrettyBlocks\Core\Components\Element;
use PrestaSafe\PrettyBlocks\Core\Components\SectionElement;
use PrestaSafe\PrettyBlocks\Core\Components\ColumnElement;
use PrestaSafe\PrettyBlocks\Core\Components\ParagraphElement;
use Hook;

class ElementManager
{
    /**
     * @var ElementManager Singleton instance
     */
    protected static $instance;
    
    /**
     * @var array Éléments enregistrés
     */
    protected $elements = [];
    
    /**
     * Constructeur privé (pattern Singleton)
     */
    private function __construct()
    {
        $this->registerDefaultElements();
        $this->registerCustomElements();
    }
    
    /**
     * Singleton pattern
     * 
     * @return ElementManager L'instance unique
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Enregistre les éléments par défaut
     */
    private function registerDefaultElements()
    {
        // $this->addElement(new SectionElement());
        // $this->addElement(new ColumnElement());
        // $this->addElement(new ParagraphElement());
        // Ajouter d'autres éléments par défaut ici
    }
    
    /**
     * Enregistre les éléments personnalisés via les hooks
     */
    private function registerCustomElements()
    {
        $customElements = Hook::exec('ActionRegisterElement', [], null, true);
        
        if (is_array($customElements)) {
            foreach ($customElements as $moduleName => $elements) {
                if (is_array($elements)) {
                    foreach ($elements as $element) {
                        if ($element instanceof Element) {
                            $this->addElement($element);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Ajoute un élément au gestionnaire
     * 
     * @param Element $element L'élément à ajouter
     * @return $this
     */
    public function addElement(Element $element)
    {
        $this->elements[$element->getCode()] = $element;
        return $this;
    }
    
    /**
     * Récupère un élément par son code
     * 
     * @param string $code Le code de l'élément à récupérer
     * @return Element|null L'élément trouvé ou null
     */
    public function getElementById($code)
    {
        return isset($this->elements[$code]) ? $this->elements[$code] : null;
    }
    
    /**
     * Récupère tous les éléments
     * 
     * @return array Les éléments
     */
    public function getAllElements()
    {
        return $this->elements;
    }
    
    /**
     * Récupère les éléments d'une catégorie spécifique
     * 
     * @param string $category La catégorie
     * @return array Les éléments de la catégorie
     */
    public function getElementsByCategory($category)
    {
        $result = [];
        
        foreach ($this->elements as $code => $element) {
            if ($element->getCategory() === $category) {
                $result[$code] = $element;
            }
        }
        
        return $result;
    }
    
    /**
     * Récupère les éléments pour l'API
     * 
     * @return array Les données des éléments pour l'API
     */
    public function getElementsForApi()
    {
        $result = [];
        
        foreach ($this->elements as $code => $element) {
            $result[$code] = $element->toArray();
        }
        
        return $result;
    }
    
    /**
     * Créer une nouvelle instance d'un élément
     * 
     * @param string $elementType Le type d'élément à créer
     * @param string|null $id L'ID à assigner à l'élément
     * @return Element|null L'élément créé ou null si le type n'existe pas
     */
    public function createElement($elementType, $id = null)
    {
        if (!isset($this->elements[$elementType])) {
            return null;
        }
        
        // Cloner le prototype d'élément
        $element = clone $this->elements[$elementType];
        
        // Assigner un nouvel ID si nécessaire
        if ($id !== null) {
            $element->setId($id);
        } else {
            $element->setId(uniqid('element_'));
        }
        
        return $element;
    }
    
    /**
     * Construit un arbre d'éléments à partir d'un tableau de données
     * 
     * @param array $elementsData Les données des éléments
     * @return array Les instances d'éléments
     */
    public function buildElementTree(array $elementsData)
    {
        $result = [];
        
        foreach ($elementsData as $elementData) {
            if (!isset($elementData['code'])) {
                continue;
            }
            
            $element = $this->createElement(
                $elementData['code'],
                $elementData['id'] ?? null
            );
            
            if (!$element) {
                continue;
            }
            
            // Appliquer les valeurs
            if (isset($elementData['values']) && is_array($elementData['values'])) {
                $element->setValues($elementData['values']);
            }
            
            // Construire récursivement les enfants
            if (isset($elementData['elements']) && is_array($elementData['elements'])) {
                $children = $this->buildElementTree($elementData['elements']);
                
                foreach ($children as $child) {
                    $element->addElement($child);
                }
            }
            
            $result[] = $element;
        }
        
        return $result;
    }
}