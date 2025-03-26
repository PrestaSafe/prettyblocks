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

namespace PrestaSafe\PrettyBlocks\Core\Components;

class ColumnElement extends Element
{
    /**
     * Constructeur de l'élément Colonne
     * 
     * @param string|null $id L'identifiant unique de l'élément
     */
    public function __construct($id = null)
    {
        $this->name = 'Column';
        $this->description = 'A column that can hold other elements';
        $this->code = 'column_element';
        $this->icon = 'TableCellsIcon';
        $this->category = 'layout';
        $this->templates = [
            'default' => 'module:prettyblocks/views/templates/elements/column.tpl'
        ];
        
        parent::__construct($id);
    }
    
    /**
     * Définit les champs de configuration de l'élément Colonne
     */
    protected function defineConfig()
    {
        // Configuration de base de la colonne
        $this->addField('width', [
            'type' => 'select',
            'label' => 'Column Width',
            'default' => '6',
            'options' => [
                '1' => '1/12',
                '2' => '2/12',
                '3' => '3/12',
                '4' => '4/12',
                '5' => '5/12',
                '6' => '6/12',
                '7' => '7/12',
                '8' => '8/12',
                '9' => '9/12',
                '10' => '10/12',
                '11' => '11/12',
                '12' => '12/12'
            ]
        ]);
        
        // Responsive settings
        $this->addField('widthTablet', [
            'type' => 'select',
            'label' => 'Tablet Width',
            'default' => '',
            'options' => [
                '' => 'Same as desktop',
                '1' => '1/12',
                '2' => '2/12',
                '3' => '3/12',
                '4' => '4/12',
                '5' => '5/12',
                '6' => '6/12',
                '7' => '7/12',
                '8' => '8/12',
                '9' => '9/12',
                '10' => '10/12',
                '11' => '11/12',
                '12' => '12/12'
            ]
        ]);
        
        $this->addField('widthMobile', [
            'type' => 'select',
            'label' => 'Mobile Width',
            'default' => '12',
            'options' => [
                '' => 'Same as tablet',
                '1' => '1/12',
                '2' => '2/12',
                '3' => '3/12',
                '4' => '4/12',
                '5' => '5/12',
                '6' => '6/12',
                '7' => '7/12',
                '8' => '8/12',
                '9' => '9/12',
                '10' => '10/12',
                '11' => '11/12',
                '12' => '12/12'
            ]
        ]);
        
        // Styling options
        $this->addField('background', [
            'type' => 'color',
            'label' => 'Background Color',
            'default' => ''
        ]);
        
        $this->addField('padding', [
            'type' => 'integer',
            'label' => 'Padding',
            'default' => 15
        ]);
        
        $this->addField('alignment', [
            'type' => 'select',
            'label' => 'Content Alignment',
            'default' => 'left',
            'options' => [
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right'
            ]
        ]);
        
        $this->addField('verticalAlignment', [
            'type' => 'select',
            'label' => 'Vertical Alignment',
            'default' => 'top',
            'options' => [
                'top' => 'Top',
                'middle' => 'Middle',
                'bottom' => 'Bottom'
            ]
        ]);
        
        // Advanced options
        $this->addField('cssClass', [
            'type' => 'text',
            'label' => 'CSS Class',
            'default' => ''
        ]);
        
        $this->addField('order', [
            'type' => 'integer',
            'label' => 'Order',
            'default' => 0,
            'description' => 'Change the visual order of the column'
        ]);
    }
    
    /**
     * Ajoute un composant à la colonne
     * 
     * @param Element $component Le composant à ajouter
     * @return $this
     */
    public function addComponent(Element $component)
    {
        return $this->addElement($component);
    }
    
    /**
     * Récupère tous les composants de la colonne
     * 
     * @return array Les composants de la colonne
     */
    public function getComponents()
    {
        return $this->getElements();
    }
    
    /**
     * Rendu spécifique pour les colonnes
     * 
     * @param array $context Contexte supplémentaire
     * @return string Le HTML généré
     */
    protected function renderFallback(array $context = [])
    {
        // Récupérer les valeurs
        $width = $this->getValue('width', '6');
        $widthTablet = $this->getValue('widthTablet', '');
        $widthMobile = $this->getValue('widthMobile', '12');
        $background = $this->getValue('background', '');
        $padding = $this->getValue('padding', 15);
        $alignment = $this->getValue('alignment', 'left');
        $verticalAlignment = $this->getValue('verticalAlignment', 'top');
        $cssClass = $this->getValue('cssClass', '');
        $order = $this->getValue('order', 0);
        
        // Préparer les styles
        $styles = [];
        
        if ($background) {
            $styles[] = 'background-color: ' . $background;
        }
        
        if ($padding !== '') {
            $styles[] = 'padding: ' . $padding . 'px';
        }
        
        if ($alignment !== 'left') {
            $styles[] = 'text-align: ' . $alignment;
        }
        
        // Préparer les classes
        $classes = ['prettyblocks-column'];
        
        // Classes de largeur responsive
        $classes[] = 'col-' . $width;
        
        if ($widthTablet) {
            $classes[] = 'col-md-' . $widthTablet;
        }
        
        if ($widthMobile) {
            $classes[] = 'col-sm-' . $widthMobile;
        }
        
        // Alignement vertical
        if ($verticalAlignment === 'middle') {
            $classes[] = 'd-flex align-items-center';
        } elseif ($verticalAlignment === 'bottom') {
            $classes[] = 'd-flex align-items-end';
        }
        
        // Ordre
        if ($order > 0) {
            $classes[] = 'order-' . $order;
        }
        
        // Classe CSS personnalisée
        if ($cssClass) {
            $classes[] = $cssClass;
        }
        
        // Générer l'HTML
        $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';
        $classAttr = ' class="' . implode(' ', $classes) . '"';
        
        $output = '<div id="' . $this->id . '"' . $classAttr . $styleAttr . '>';
        
        // Rendu des composants (enfants)
        foreach ($this->getElements() as $component) {
            $output .= $component->render($context);
        }
        
        $output .= '</div>';
        
        return $output;
    }
}