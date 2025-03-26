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

class ParagraphElement extends Component
{
    /**
     * Constructeur de l'élément Paragraphe
     * 
     * @param string|null $id L'identifiant unique de l'élément
     */
    public function __construct($id = null)
    {
        $this->name = 'Paragraph';
        $this->description = 'A simple paragraph of text';
        $this->code = 'paragraph_element';
        $this->icon = 'ChatBubbleBottomCenterTextIcon';
        $this->category = 'content';
        $this->templates = [
            'default' => 'module:prettyblocks/views/templates/elements/paragraph.tpl'
        ];
        
        parent::__construct($id);
    }
    
    /**
     * Définit les champs de configuration de l'élément Paragraphe
     */
    protected function defineConfig()
    {
        // Contenu principal
        $this->addField('content', [
            'type' => 'editor',
            'label' => 'Content',
            'default' => '<p>Enter your text here...</p>'
        ]);
        
        // Options de style de texte
        $this->addField('textColor', [
            'type' => 'color',
            'label' => 'Text Color',
            'default' => ''
        ]);
        
        $this->addField('fontSize', [
            'type' => 'select',
            'label' => 'Font Size',
            'default' => 'normal',
            'options' => [
                'small' => 'Small',
                'normal' => 'Normal',
                'large' => 'Large',
                'xlarge' => 'Extra Large'
            ]
        ]);
        
        $this->addField('fontWeight', [
            'type' => 'select',
            'label' => 'Font Weight',
            'default' => 'normal',
            'options' => [
                'light' => 'Light',
                'normal' => 'Normal',
                'medium' => 'Medium',
                'semibold' => 'Semi-Bold',
                'bold' => 'Bold'
            ]
        ]);
        
        $this->addField('alignment', [
            'type' => 'select',
            'label' => 'Text Alignment',
            'default' => 'left',
            'options' => [
                'left' => 'Left',
                'center' => 'Center',
                'right' => 'Right',
                'justify' => 'Justify'
            ]
        ]);
        
        $this->addField('lineHeight', [
            'type' => 'select',
            'label' => 'Line Height',
            'default' => 'normal',
            'options' => [
                'compact' => 'Compact',
                'normal' => 'Normal',
                'relaxed' => 'Relaxed',
                'loose' => 'Loose'
            ]
        ]);
        
        // Options avancées
        $this->addField('dropCap', [
            'type' => 'checkbox',
            'label' => 'Drop Cap',
            'default' => false,
            'description' => 'Apply a drop cap to the first letter'
        ]);
        
        $this->addField('enableCustomId', [
            'type' => 'checkbox',
            'label' => 'Enable Custom ID',
            'default' => false
        ]);
        
        $this->addField('customId', [
            'type' => 'text',
            'label' => 'Custom ID',
            'default' => '',
            'description' => 'Add a custom ID to this paragraph (for anchor links)'
        ]);
        
        // Ajouter les champs communs à tous les composants
        $this->addCommonComponentFields();
    }
    
    /**
     * Rendu spécifique du contenu du paragraphe
     * 
     * @param array $context Contexte supplémentaire
     * @return string Le HTML du contenu du paragraphe
     */
    protected function renderComponentContent(array $context = [])
    {
        // Récupérer les valeurs des champs
        $content = $this->getValue('content', '<p>Enter your text here...</p>');
        $textColor = $this->getValue('textColor', '');
        $fontSize = $this->getValue('fontSize', 'normal');
        $fontWeight = $this->getValue('fontWeight', 'normal');
        $alignment = $this->getValue('alignment', 'left');
        $lineHeight = $this->getValue('lineHeight', 'normal');
        $dropCap = $this->getValue('dropCap', false);
        
        // Préparer les styles spécifiques au texte
        $styles = [];
        
        if ($textColor) {
            $styles[] = 'color: ' . $textColor;
        }
        
        // Mapping des tailles de police
        $fontSizeMap = [
            'small' => '0.875rem',
            'normal' => '1rem',
            'large' => '1.25rem',
            'xlarge' => '1.5rem'
        ];
        
        if (isset($fontSizeMap[$fontSize])) {
            $styles[] = 'font-size: ' . $fontSizeMap[$fontSize];
        }
        
        // Mapping des épaisseurs de police
        $fontWeightMap = [
            'light' => '300',
            'normal' => '400',
            'medium' => '500',
            'semibold' => '600',
            'bold' => '700'
        ];
        
        if (isset($fontWeightMap[$fontWeight])) {
            $styles[] = 'font-weight: ' . $fontWeightMap[$fontWeight];
        }
        
        if ($alignment !== 'left') {
            $styles[] = 'text-align: ' . $alignment;
        }
        
        // Mapping des hauteurs de ligne
        $lineHeightMap = [
            'compact' => '1.2',
            'normal' => '1.5',
            'relaxed' => '1.8',
            'loose' => '2'
        ];
        
        if (isset($lineHeightMap[$lineHeight])) {
            $styles[] = 'line-height: ' . $lineHeightMap[$lineHeight];
        }
        
        // Classes pour le paragraphe
        $classes = ['prettyblocks-paragraph'];
        
        if ($dropCap) {
            $classes[] = 'drop-cap';
            // Appliquer un style spécifique pour la première lettre
            $styles[] = '--drop-cap-color: ' . ($textColor ?: 'inherit');
        }
        
        // ID personnalisé
        $idAttr = '';
        if ($this->getValue('enableCustomId', false) && $this->getValue('customId', '')) {
            $idAttr = ' id="' . $this->getValue('customId') . '"';
        }
        
        // Générer l'HTML
        $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';
        $classAttr = ' class="' . implode(' ', $classes) . '"';
        
        return '<div' . $idAttr . $classAttr . $styleAttr . '>' . $content . '</div>';
    }
}