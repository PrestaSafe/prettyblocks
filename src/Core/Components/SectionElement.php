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

class SectionElement extends Element
{
    /**
     * Constructeur de l'élément Section
     * 
     * @param string|null $id L'identifiant unique de l'élément
     */
    public function __construct($id = null)
    {
        $this->name = 'Section';
        $this->description = 'A container that can hold columns';
        $this->code = 'section_element';
        $this->icon = 'SquaresPlusIcon';
        $this->category = 'layout';
        $this->templates = [
            'default' => 'module:prettyblocks/views/templates/elements/section.tpl'
        ];
        
        parent::__construct($id);
    }
    
    /**
     * Définit les champs de configuration de l'élément Section
     */
    protected function defineConfig()
    {
        // Configuration de base de la section
        $this->addField('title', [
            'type' => 'text',
            'label' => 'Section Title',
            'default' => 'New Section'
        ]);
        
        $this->addField('background', [
            'type' => 'color',
            'label' => 'Background Color',
            'default' => ''
        ]);
        
        $this->addField('container', [
            'type' => 'checkbox',
            'label' => 'Use Container',
            'default' => true
        ]);
        
        // Champs pour les paddings
        $this->addField('paddingTop', [
            'type' => 'integer',
            'label' => 'Padding Top',
            'default' => 20
        ]);
        
        $this->addField('paddingBottom', [
            'type' => 'integer',
            'label' => 'Padding Bottom',
            'default' => 20
        ]);
        
        $this->addField('paddingLeft', [
            'type' => 'integer',
            'label' => 'Padding Left',
            'default' => 0
        ]);
        
        $this->addField('paddingRight', [
            'type' => 'integer',
            'label' => 'Padding Right',
            'default' => 0
        ]);
        
        // Options avancées
        $this->addField('fullWidth', [
            'type' => 'checkbox',
            'label' => 'Full Width',
            'default' => false
        ]);
        
        $this->addField('cssClass', [
            'type' => 'text',
            'label' => 'CSS Class',
            'default' => ''
        ]);
        
        $this->addField('backgroundImage', [
            'type' => 'fileupload',
            'label' => 'Background Image',
            'path' => '$/modules/prettyblocks/views/images/',
            'default' => [
                'url' => ''
            ]
        ]);
        
        $this->addField('parallax', [
            'type' => 'checkbox',
            'label' => 'Enable Parallax Effect',
            'default' => false
        ]);
    }
    
    /**
     * Ajoute une colonne à la section
     * 
     * @param ColumnElement $column La colonne à ajouter
     * @return $this
     */
    public function addColumn(ColumnElement $column)
    {
        return $this->addElement($column);
    }
    
    /**
     * Récupère toutes les colonnes de la section
     * 
     * @return array Les colonnes de la section
     */
    public function getColumns()
    {
        return $this->getElements();
    }
    
    /**
     * Rendu spécifique pour les sections
     * 
     * @param array $context Contexte supplémentaire
     * @return string Le HTML généré
     */
    protected function renderFallback(array $context = [])
    {
        // Récupérer les valeurs
        $background = $this->getValue('background', '');
        $paddingTop = $this->getValue('paddingTop', 20);
        $paddingRight = $this->getValue('paddingRight', 0);
        $paddingBottom = $this->getValue('paddingBottom', 20);
        $paddingLeft = $this->getValue('paddingLeft', 0);
        $fullWidth = $this->getValue('fullWidth', false);
        $cssClass = $this->getValue('cssClass', '');
        $backgroundImage = $this->getValue('backgroundImage', ['url' => '']);
        $parallax = $this->getValue('parallax', false);
        
        // Préparer les styles
        $styles = [];
        
        if ($background) {
            $styles[] = 'background-color: ' . $background;
        }
        
        if (!empty($backgroundImage['url'])) {
            $styles[] = 'background-image: url(' . $backgroundImage['url'] . ')';
            $styles[] = 'background-size: cover';
            $styles[] = 'background-position: center';
            
            if ($parallax) {
                $styles[] = 'background-attachment: fixed';
            }
        }
        
        $styles[] = 'padding: ' . $paddingTop . 'px ' . $paddingRight . 'px ' . 
                   $paddingBottom . 'px ' . $paddingLeft . 'px';
        
        // Préparer les classes
        $classes = ['prettyblocks-section'];
        
        if ($fullWidth) {
            $classes[] = 'full-width';
        }
        
        if ($cssClass) {
            $classes[] = $cssClass;
        }
        
        // Générer l'HTML
        $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';
        $classAttr = ' class="' . implode(' ', $classes) . '"';
        
        $output = '<section id="' . $this->id . '"' . $classAttr . $styleAttr . '>';
        
        if ($this->getValue('container', true)) {
            $output .= '<div class="container">';
        }
        
        // Titre de la section si présent
        if ($this->getValue('title')) {
            $output .= '<h2 class="section-title">' . $this->getValue('title') . '</h2>';
        }
        
        // Rendu des colonnes (enfants)
        $output .= '<div class="row">';
        foreach ($this->getElements() as $column) {
            $output .= $column->render($context);
        }
        $output .= '</div>';
        
        if ($this->getValue('container', true)) {
            $output .= '</div>';
        }
        
        $output .= '</section>';
        
        return $output;
    }
}