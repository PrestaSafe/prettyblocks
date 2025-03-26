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

abstract class Element
{
    protected $id;
    protected $name;
    protected $description;
    protected $code;
    protected $icon;
    protected $icon_path;
    protected $need_reload = true;
    protected $nameFrom;
    protected $category = 'element';
    protected $templates = [];
    protected $config = [
        'fields' => []
    ];
    protected $elements = []; // Pour stocker les sous-éléments
    protected $values = []; // Pour stocker les valeurs des champs
    protected $context;

    /**
     * Constructeur de l'élément
     * 
     * @param string|null $id L'identifiant unique de l'élément
     */
    public function __construct($id = null)
    {
        $this->id = $id ?: uniqid('element_');
        $this->context = \Context::getContext();
        $this->defineConfig(); // Méthode à implémenter dans les classes enfants
    }

    /**
     * Méthode à implémenter pour définir la configuration de l'élément
     */
    abstract protected function defineConfig();

    /**
     * Ajoute un champ à la configuration
     * 
     * @param string $key Clé du champ
     * @param array $fieldConfig Configuration du champ
     * @return $this
     */
    protected function addField($key, array $fieldConfig)
    {
        $this->config['fields'][$key] = $fieldConfig;
        
        // Initialiser la valeur par défaut si elle existe
        if (isset($fieldConfig['default'])) {
            $this->values[$key] = $fieldConfig['default'];
        }
        
        return $this;
    }

    /**
     * Ajoute un sous-élément
     * 
     * @param Element $element L'élément à ajouter
     * @return $this
     */
    public function addElement(Element $element)
    {
        $this->elements[] = $element;
        return $this;
    }

    /**
     * Récupère tous les sous-éléments
     * 
     * @return array Les sous-éléments
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Définit l'ID de l'élément
     * 
     * @param string $id Le nouvel ID
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Récupère l'ID de l'élément
     * 
     * @return string L'ID de l'élément
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Récupère le nom de l'élément
     * 
     * @return string Le nom de l'élément
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Définit le nom de l'élément
     * 
     * @param string $name Le nouveau nom
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Récupère la description de l'élément
     * 
     * @return string La description de l'élément
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Définit la description de l'élément
     * 
     * @param string $description La nouvelle description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Récupère le code de l'élément
     * 
     * @return string Le code de l'élément
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Définit le code de l'élément
     * 
     * @param string $code Le nouveau code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Récupère l'icône de l'élément
     * 
     * @return string L'icône de l'élément
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Définit l'icône de l'élément
     * 
     * @param string $icon La nouvelle icône
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Récupère le chemin de l'icône de l'élément
     * 
     * @return string Le chemin de l'icône
     */
    public function getIconPath()
    {
        return $this->icon_path;
    }

    /**
     * Définit le chemin de l'icône de l'élément
     * 
     * @param string $icon_path Le nouveau chemin d'icône
     * @return $this
     */
    public function setIconPath($icon_path)
    {
        $this->icon_path = $icon_path;
        return $this;
    }

    /**
     * Vérifie si l'élément nécessite un rechargement
     * 
     * @return bool True si l'élément nécessite un rechargement, false sinon
     */
    public function getNeedReload()
    {
        return $this->need_reload;
    }

    /**
     * Définit si l'élément nécessite un rechargement
     * 
     * @param bool $need_reload Si l'élément nécessite un rechargement
     * @return $this
     */
    public function setNeedReload($need_reload)
    {
        $this->need_reload = (bool) $need_reload;
        return $this;
    }

    /**
     * Récupère le champ utilisé pour nommer l'élément
     * 
     * @return string Le champ utilisé pour nommer l'élément
     */
    public function getNameFrom()
    {
        return $this->nameFrom;
    }

    /**
     * Définit le champ utilisé pour nommer l'élément
     * 
     * @param string $nameFrom Le champ à utiliser
     * @return $this
     */
    public function setNameFrom($nameFrom)
    {
        $this->nameFrom = $nameFrom;
        return $this;
    }

    /**
     * Récupère la catégorie de l'élément
     * 
     * @return string La catégorie de l'élément
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Définit la catégorie de l'élément
     * 
     * @param string $category La nouvelle catégorie
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Récupère les templates disponibles pour l'élément
     * 
     * @return array Les templates
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Ajoute un template
     * 
     * @param string $key La clé du template
     * @param string $path Le chemin du template
     * @return $this
     */
    public function addTemplate($key, $path)
    {
        $this->templates[$key] = $path;
        return $this;
    }

    /**
     * Définit les templates de l'élément
     * 
     * @param array $templates Les nouveaux templates
     * @return $this
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
        return $this;
    }

    /**
     * Récupère la configuration de l'élément
     * 
     * @return array La configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Définit les valeurs de l'élément
     * 
     * @param array $values Les valeurs à définir
     * @return $this
     */
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setValue($key, $value);
        }
        return $this;
    }

    /**
     * Définit une valeur spécifique
     * 
     * @param string $key La clé de la valeur
     * @param mixed $value La valeur à définir
     * @return $this
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
        return $this;
    }

    /**
     * Récupère toutes les valeurs
     * 
     * @return array Les valeurs
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Récupère une valeur spécifique
     * 
     * @param string $key La clé de la valeur à récupérer
     * @param mixed $default La valeur par défaut si la clé n'existe pas
     * @return mixed La valeur
     */
    public function getValue($key, $default = null)
    {
        return isset($this->values[$key]) ? $this->values[$key] : $default;
    }

    /**
     * Convertit l'élément en tableau compatible avec PrettyBlocks
     * 
     * @return array Les données de l'élément
     */
    public function toArray()
    {
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'icon' => $this->icon,
            'icon_path' => $this->icon_path,
            'need_reload' => $this->need_reload,
            'category' => $this->category,
            'templates' => $this->templates,
            'config' => $this->config,
            'values' => $this->values,
            'id' => $this->id
        ];

        if ($this->nameFrom) {
            $data['nameFrom'] = $this->nameFrom;
        }

        // Ajouter les sous-éléments s'il y en a
        if (!empty($this->elements)) {
            $data['elements'] = [];
            foreach ($this->elements as $element) {
                $data['elements'][] = $element->toArray();
            }
        }

        return $data;
    }

    /**
     * Rend l'élément en HTML
     * 
     * @param array $context Contexte supplémentaire pour le rendu
     * @return string Le HTML généré
     */
    public function render(array $context = [])
    {
        // Préparer les variables pour le template
        $variables = [
            'element' => $this,
            'id' => $this->getId(),
            'values' => $this->getValues(),
            'children' => $this->getElements(),
            'context' => $context
        ];
        
        // Assigner les variables à Smarty
        foreach ($variables as $key => $value) {
            $this->context->smarty->assign($key, $value);
        }
        
        // Récupérer le template à utiliser
        $template = isset($this->templates['default']) ? $this->templates['default'] : null;
        
        if (!$template || !$this->context->smarty->templateExists($template)) {
            // Fallback si le template n'existe pas
            return $this->renderFallback($context);
        }
        
        // Rendre le template
        return $this->context->smarty->fetch($template);
    }

    /**
     * Rendu de secours si le template n'existe pas
     * 
     * @param array $context Contexte supplémentaire
     * @return string Le HTML généré
     */
    protected function renderFallback(array $context = [])
    {
        $output = '<div id="' . $this->id . '" class="prettyblocks-element prettyblocks-element-' . $this->code . '">';
        $output .= '<div class="element-header">' . $this->name . '</div>';
        $output .= '<div class="element-content">';
        
        // Rendre les enfants si présents
        if (!empty($this->elements)) {
            foreach ($this->elements as $element) {
                $output .= $element->render($context);
            }
        }
        
        $output .= '</div></div>';
        
        return $output;
    }
}