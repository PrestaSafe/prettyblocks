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

abstract class Component extends Element
{
    /**
     * Constructeur de base pour les composants
     * 
     * @param string|null $id L'identifiant unique du composant
     */
    public function __construct($id = null)
    {
        $this->category = 'component';
        parent::__construct($id);
    }
    
    /**
     * Ajoute des champs de configuration communs à tous les composants
     */
    protected function addCommonComponentFields()
    {
        // Marge
        $this->addField('marginTop', [
            'type' => 'integer',
            'label' => 'Margin Top',
            'default' => 0
        ]);
        
        $this->addField('marginBottom', [
            'type' => 'integer',
            'label' => 'Margin Bottom',
            'default' => 0
        ]);
        
        $this->addField('marginLeft', [
            'type' => 'integer',
            'label' => 'Margin Left',
            'default' => 0
        ]);
        
        $this->addField('marginRight', [
            'type' => 'integer',
            'label' => 'Margin Right',
            'default' => 0
        ]);
        
        // Visibilité responsive
        $this->addField('visibleDesktop', [
            'type' => 'checkbox',
            'label' => 'Visible on Desktop',
            'default' => true
        ]);
        
        $this->addField('visibleTablet', [
            'type' => 'checkbox',
            'label' => 'Visible on Tablet',
            'default' => true
        ]);
        
        $this->addField('visibleMobile', [
            'type' => 'checkbox',
            'label' => 'Visible on Mobile',
            'default' => true
        ]);
        
        // Classe CSS supplémentaire
        $this->addField('cssClass', [
            'type' => 'text',
            'label' => 'CSS Class',
            'default' => ''
        ]);
        
        // Animation
        $this->addField('animation', [
            'type' => 'select',
            'label' => 'Animation',
            'default' => 'none',
            'options' => [
                'none' => 'None',
                'fade-in' => 'Fade In',
                'slide-up' => 'Slide Up',
                'slide-down' => 'Slide Down',
                'slide-left' => 'Slide Left',
                'slide-right' => 'Slide Right',
                'zoom-in' => 'Zoom In',
                'zoom-out' => 'Zoom Out'
            ]
        ]);
        
        $this->addField('animationDelay', [
            'type' => 'integer',
            'label' => 'Animation Delay (ms)',
            'default' => 0
        ]);
        
        $this->addField('animationDuration', [
            'type' => 'integer',
            'label' => 'Animation Duration (ms)',
            'default' => 500
        ]);
    }
    
    /**
     * Prépare les attributs HTML communs à tous les composants
     * 
     * @return array Les classes et styles communs
     */
    protected function getCommonAttributes()
    {
        $classes = ['prettyblocks-component', 'component-' . $this->code];
        $styles = [];
        
        // Appliquer les marges
        $marginTop = $this->getValue('marginTop', 0);
        $marginBottom = $this->getValue('marginBottom', 0);
        $marginLeft = $this->getValue('marginLeft', 0);
        $marginRight = $this->getValue('marginRight', 0);
        
        if ($marginTop || $marginBottom || $marginLeft || $marginRight) {
            $styles[] = 'margin: ' . $marginTop . 'px ' . $marginRight . 'px ' . 
                        $marginBottom . 'px ' . $marginLeft . 'px';
        }
        
        // Appliquer la classe CSS personnalisée
        $cssClass = $this->getValue('cssClass', '');
        if ($cssClass) {
            $classes[] = $cssClass;
        }
        
        // Appliquer les classes de visibilité responsive
        $visibleDesktop = $this->getValue('visibleDesktop', true);
        $visibleTablet = $this->getValue('visibleTablet', true);
        $visibleMobile = $this->getValue('visibleMobile', true);
        
        if (!$visibleDesktop) {
            $classes[] = 'd-none d-md-none d-lg-none d-xl-none';
        }
        
        if (!$visibleTablet) {
            $classes[] = 'd-md-none';
        }
        
        if (!$visibleMobile) {
            $classes[] = 'd-none d-sm-block';
        }
        
        // Appliquer l'animation
        $animation = $this->getValue('animation', 'none');
        if ($animation !== 'none') {
            $classes[] = 'animated ' . $animation;
            $styles[] = '--animation-delay: ' . $this->getValue('animationDelay', 0) . 'ms';
            $styles[] = '--animation-duration: ' . $this->getValue('animationDuration', 500) . 'ms';
        }
        
        return [
            'classes' => $classes,
            'styles' => $styles
        ];
    }
    
    /**
     * Rendu générique pour un composant
     * 
     * @param array $context Contexte supplémentaire
     * @return string Le HTML généré
     */
    protected function renderFallback(array $context = [])
    {
        $attributes = $this->getCommonAttributes();
        $classes = $attributes['classes'];
        $styles = $attributes['styles'];
        
        $classAttr = ' class="' . implode(' ', $classes) . '"';
        $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';
        
        $output = '<div id="' . $this->id . '"' . $classAttr . $styleAttr . '>';
        $output .= '<div class="component-content">';
        $output .= $this->renderComponentContent($context);
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Rendu spécifique du contenu du composant, à implémenter dans les classes enfants
     * 
     * @param array $context Contexte supplémentaire
     * @return string Le HTML du contenu du composant
     */
    abstract protected function renderComponentContent(array $context = []);
}