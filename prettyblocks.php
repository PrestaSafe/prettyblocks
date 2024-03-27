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
use PrestaSafe\PrettyBlocks\Core\Components\Title;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollectionInterface;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;

class PrettyBlocks extends Module implements WidgetInterface
{
    public $js_path;
    public $css_path;
    public $dev_ps = true;
    public $valid_types = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'pdf'];
    public $upload_dir = __DIR__ . '/views/images/';
    public $form_trans = [];
    public $tabs = [
        [
            'name' => 'Pretty Blocks', // One name for all langs
            'class_name' => 'AdminThemeManagerController',
            'visible' => true,
            'parent_class_name' => 'IMPROVE',
        ],
    ];

    public $hooks = [
        'displayHome',
        'displayFooter',
        'displayLeftColumn',
        'displayRightColumn',
        'displayHeader',
        'actionDispatcher',
        'actionFrontControllerSetVariables',
        'ActionRegisterThemeSettings',
        'displayBackOfficeHeader',
        'ActionRegisterBlock',
        'ActionProductGridDefinitionModifier',
        'ActionCategoryGridDefinitionModifier',
        'ActionCmsPageGridDefinitionModifier',
    ];

    public function __construct()
    {
        $this->name = 'prettyblocks';
        $this->tab = 'administration';
        $this->version = '3.1.0';
        $this->author = 'PrestaSafe';
        $this->need_instance = 1;
        $this->js_path = $this->_path . 'views/js/';
        $this->css_path = $this->_path . 'views/css/';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Pretty Blocks', [], 'Modules.Prettyblocks.Admin');
        $this->description = $this->trans('Configure your design easily', [], 'Modules.Prettyblocks.Admin');
        $this->controllers = ['ajax'];

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /**
     * Add button prettyblocks to product grid
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionProductGridDefinitionModifier(array $params): void
    {
        /** @var GridDefinitionInterface $gridDefinition */
        $gridDefinition = $params['definition'];
        $this->_generateButtonPrettyBLocks($gridDefinition, 'product', 'id_product');
    }

    /**
     * Add button prettyblocks to category grid
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionCategoryGridDefinitionModifier(array $params): void
    {
        /** @var GridDefinitionInterface $gridDefinition */
        $gridDefinition = $params['definition'];
        $this->_generateButtonPrettyBLocks($gridDefinition, 'category', 'id_category');
    }

    /**
     * Add button prettyblocks to cms grid
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionCmsPageGridDefinitionModifier(array $params): void
    {
        /** @var GridDefinitionInterface $gridDefinition */
        $gridDefinition = $params['definition'];
        $this->_generateButtonPrettyBLocks($gridDefinition, 'cms', 'id_cms');
    }

    // actionCmsPageCategoryGridDefinitionModifier

    /**
     * Add button prettyblocks to product grid
     *
     * @param GridDefinitionInterface $definition
     *
     * @return GridDefinitionInterface
     */
    private function _generateButtonPrettyBLocks($definition, $endpoint = 'custom', $field = 'id_product')
    {
        /** @var RowActionCollectionInterface $actionsCollection */
        $prettyblocksImg = HelperBuilder::pathFormattedToUrl('$/modules/prettyblocks/logo.png');
        $columnCollection = (new PrestaSafe\PrettyBlocks\Core\Grid\Column\Type\PrettyBlocksButtonColumn('edit2'))
            ->setName($this->trans('Edit 2', [], 'Admin.Actions'))
            ->setOptions([
                'route' => 'admin_prettyblocks',
                'route_param_name' => 'id',
                'route_param_field' => $field,
                'icon' => $prettyblocksImg,
                'field' => $field,
                'endpoint' => $endpoint,
                'attr' => [
                    'action' => 'view',
                    'class' => 'btn btn-prettyblocks text-white',
                ],
                // 'clickable_row' => true,
            ]);

        return $definition->getColumns()->add($columnCollection);
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * create tables on install.
     *
     * @return bool
     */
    private function createBlockDb()
    {
        $db = [];
        $db[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'prettyblocks` (
            `id_prettyblocks` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `instance_id` text DEFAULT NULL,
            `config` longtext DEFAULT NULL,
            `code` varchar(255) DEFAULT NULL,
            `template` longtext DEFAULT NULL,
            `default_params` longtext DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,   
            `zone_name` varchar(255) DEFAULT NULL,
            `position` int(11) DEFAULT 0,
            `date_add` datetime DEFAULT NULL,
            `date_upd` datetime DEFAULT NULL,
            `id_shop` int(11) DEFAULT NULL,
            `id_lang` int(11) DEFAULT NULL,
            `state` longtext,          
            PRIMARY KEY (`id_prettyblocks`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $isOk = true;
        foreach ($db as $sql) {
            $isOk &= Db::getInstance()->execute($sql);
        }
        $isOk &= $this->makeSettingsTable();

        return $isOk;
    }

    public function makeSettingsTable()
    {
        $sql = 'CREATE TABLE `' . _DB_PREFIX_ . 'prettyblocks_settings` (
            `id_prettyblocks_settings` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `theme_name` varchar(255) DEFAULT NULL,
            `profile` varchar(255) DEFAULT NULL,
            `id_shop` int(11) DEFAULT NULL,
            `settings` longtext,
            PRIMARY KEY (`id_prettyblocks_settings`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Remove DB on uninstall.
     *
     * @return bool
     */
    private function removeDb()
    {
        $db = [];
        $db[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'prettyblocks`';
        $db[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'prettyblocks_lang`';
        $db[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'prettyblocks_settings`';

        $isOk = true;
        foreach ($db as $sql) {
            $isOk &= Db::getInstance()->execute($sql);
        }

        return $isOk;
    }

    public function getContent()
    {
        // $this->registerHook('displayBackOfficeHeader');
        return Tools::redirect($this->getPrettyBlocksUrl());
    }

    private function getPrettyBlocksUrl()
    {
        $domain = Tools::getShopDomainSsl(true);

        return $domain . Link::getUrlSmarty([
            'entity' => 'sf',
            'route' => 'admin_prettyblocks',
        ]);
    }

    private function loadDefault()
    {
        return Configuration::updateGlobalValue('_PRETTYBLOCKS_TOKEN_', Tools::passwdGen(25));
    }

    public function hookdisplayBackOfficeHeader($params)
    {
        $route = (new Link())->getAdminLink('AdminThemeManagerControllerRouteGenerator');
        Media::addJsDef([
            'prettyblocks_route_generator' => $route,
            'prettyblocks_logo' => HelperBuilder::pathFormattedToUrl('$/modules/prettyblocks/logo.png'),
            'ps_version' => _PS_VERSION_,
            'ps17' => version_compare(_PS_VERSION_, '8.0.0', '<='),
            'ps8' => version_compare(_PS_VERSION_, '8.0.0', '>='),
        ]);
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');

        // $this->context->controller->addJS($this->_path . 'views/js/back.js');
    }

    public function install()
    {
        return parent::install()
            && $this->loadDefault()
            && $this->createBlockDb()
            && $this->registerHook($this->hooks);
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->removeDb();
    }

    public function hookActionFrontControllerSetVariables()
    {
        return [
            // 'ajax_builder_url' => $this->context->link->getModuleLink($this->name,'ajax'),
            'theme_settings' => PrettyBlocksModel::getThemeSettings(false, 'front'),
            'id_shop' => (int) $this->context->shop->id,
            'shop_name' => $this->context->shop->name,
            'shop_current_url' => $this->context->shop->getBaseURL(true, true),
        ];
    }

    /**
     * Generate $state to block view
     *
     * @return array
     */
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $block = (isset($configuration['block'])) ? PrettyBlocksModel::loadBlock($configuration['block']) : [];

        return [
            'block' => $block,
            'hookName' => $hookName,
            'configuration' => $configuration,
        ];
    }

    private function _addDynamicZones()
    {
        $smartyVars = $this->context->smarty->getTemplateVars();

        if ($this->context->controller->php_self == 'product') {
            // product description
            if (isset($smartyVars['product']['description'])) {
                $product = $smartyVars['product'];
                $zone_name = 'product-description-' . $smartyVars['product']['id_product'];
                // si no blocks on this zone, feed product description
                if (!HelperBuilder::zoneHasBlock($zone_name)) {
                    $this->registerBlockToZone($zone_name, 'prettyblocks_product_description');
                }
                $description = $this->renderZone(
                    [
                        'zone_name' => $zone_name,
                        'priority' => true,
                        'alias' => 'Description produit',
                    ]
                );
                $product['description'] = $description;
                $this->context->smarty->assign('product', $product);
            }

            // product description short
            if (isset($smartyVars['product']['description_short'])) {
                $product = $smartyVars['product'];
                $zone_name = 'product-description-short-' . $smartyVars['product']['id_product'];
                // si no blocks on this zone, feed product description
                if (!HelperBuilder::zoneHasBlock($zone_name)) {
                    $this->registerBlockToZone($zone_name, 'prettyblocks_product_description_short');
                }
                $description_short = $this->renderZone(
                    [
                        'zone_name' => $zone_name,
                        'priority' => false,
                        'alias' => 'Description courte produit',
                    ]
                );
                $product['description_short'] = $description_short;
                $this->context->smarty->assign('product', $product);
            }
        }

        if ($this->context->controller->php_self == 'category') {
            // categories
            if (isset($smartyVars['category'])) {
                $category = $smartyVars['category'];
                $zone_name = 'category-description-' . $smartyVars['category']['id'];
                // si no blocks on this zone, feed product description
                if (!HelperBuilder::zoneHasBlock($zone_name)) {
                    $this->registerBlockToZone($zone_name, 'prettyblocks_category_description');
                }
                $description = $this->renderZone(
                    [
                        'zone_name' => $zone_name,
                        'priority' => true,
                        'alias' => 'Description catégorie',
                    ]
                );
                $category['description'] = $description;
                $this->context->smarty->assign('category', $category);
            }
        }
        // cms
        if ($this->context->controller->php_self == 'cms') {
            if (isset($smartyVars['cms'])) {
                $cms = $smartyVars['cms'];
                $zone_name = 'cms-description-' . $smartyVars['cms']['id'];
                // si no blocks on this zone, feed product description
                if (!HelperBuilder::zoneHasBlock($zone_name)) {
                    $this->registerBlockToZone($zone_name, 'prettyblocks_cms_content');
                }
                $description = $this->renderZone(
                    [
                        'zone_name' => $zone_name,
                        'priority' => true,
                        'alias' => 'Description CMS',
                    ]
                );
                $cms['content'] = $description;
                $this->context->smarty->assign('cms', $cms);
            }
        }
    }

    public function hookdisplayHeader($params)
    {
        $this->_addDynamicZones();
        if ((isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'iframe') || Tools::getValue('prettyblocks') === '1') {
            $this->context->controller->registerJavascript(
                'prettyblocks',
                'modules/' . $this->name . '/views/js/build/build.js',
                [
                    'position' => 'bottom',
                    'priority' => 150,
                ]
            );
            $this->context->controller->registerStylesheet(
                'prettyblocks',
                'modules/' . $this->name . '/views/css/iframe.css',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );
            $this->context->smarty->assign([
                'prettyblocks' => true,
            ]);
            // todo register css and js on iframe only from Hook
        }
        $this->context->controller->registerStylesheet(
            'tiny-slider-css',
            'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/tiny-slider.css',
            [
                'media' => 'all',
                'priority' => 200,
                'server' => 'remote', // added remote option
            ]
        );

        $this->context->controller->registerJavascript(
            'tiny-slider-js',
            'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.2/min/tiny-slider.js',
            [
                'media' => 'all',
                'priority' => 150,
                'server' => 'remote', // added remote option
            ]
        );

        $this->context->controller->registerJavascript(
            'prettyblocks-init',
            'modules/' . $this->name . '/views/js/front.js',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );
    }

    /**
     * Return la view
     */
    public function renderWidget($hookName = null, array $configuration = [])
    {
        $vars = $this->getWidgetVariables($hookName, $configuration);
        $this->smarty->assign($vars);
        if (isset($configuration['zone_name'])) {
            return $this->renderZone(['zone_name' => pSQL($configuration['zone_name'])]);
        }
        if (isset($configuration['action']) && $configuration['action'] == 'GetBlockRender') {
            $block = $configuration['data'];
            $vars = [
                'id_prettyblocks' => $block['id_prettyblocks'],
                'instance_id' => $block['instance_id'],
                'state' => $block['repeater_db'],
                'block' => $block,
                'test' => Hook::exec('beforeRenderingBlock', ['state' => $configuration['data']], null, true),
            ];
            $this->smarty->assign($vars);
            $template = $block['templates'][$block['templateSelected']] ?? 'module:prettyblocks/views/templates/blocks/welcome.tpl';

            return $this->fetch($template);
        }
        if ($vars['hookName'] !== null) {
            return $this->renderZone(['zone_name' => $vars['hookName']]);
        }
    }

    public function registerBlockToZone($zone_name, $block_code)
    {
        return PrettyBlocksModel::registerBlockToZone($zone_name, $block_code);
    }

    /**
     * Hook dispatcher for registering smarty function
     */
    public function hookActionDispatcher()
    {
        /* @deprecated {magic_zone} is deprecated since v1.1.0. Use {prettyblocks_zone} instead. */
        $this->context->smarty->registerPlugin('function', 'magic_zone', [PrettyBlocks::class, 'renderZone']);
        $this->context->smarty->registerPlugin('function', 'prettyblocks_zone', [PrettyBlocks::class, 'renderZone']);
        $this->context->smarty->registerPlugin('function', 'prettyblocks_title', [PrettyBlocks::class, 'renderTitle']);
    }

    /**
     * Render dynamic title
     *
     * @param array $params
     *
     * @return string
     */
    public static function renderTitle($params)
    {
        $tag = $params['tag'] ?? null;
        $value = $params['value'] ?? '';
        $field = $params['field'];
        $block = $params['block'];
        $classes = $params['classes'] ?? [];

        $title = new Title($tag, $classes, $block, $field);
        if (isset($params['index'])) {
            $title->setIndex((int) $params['index']);
        }

        return $title->setValueFromBlock(true)
                ->setValue($value)->render();
    }

    public static function renderZone($params)
    {
        $zone_name = $params['zone_name'];
        $priority = $params['priority'] ?? false;
        $alias = $params['alias'] ?? '';

        if (empty($zone_name)) {
            return false;
        }

        $context = Context::getContext();
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        $blocks = PrettyBlocksModel::getInstanceByZone($zone_name, 'front', $id_lang, $id_shop);

        $context->smarty->assign([
            'zone_name' => $zone_name,
            'priority' => $priority,
            'alias' => $alias,
            'blocks' => $blocks,
        ]);

        return $context->smarty->fetch('module:prettyblocks/views/templates/front/zone.tpl');
    }

    /**
     * Hook for adding theme settings
     * quick fix for adding tinyMCE api key.
     */
    public function hookActionRegisterThemeSettings()
    {
        return [
            'tinymce_api_key' => [
                'type' => 'text', // type of field
                'label' => $this->l('TinyMCE api key'), // label to display
                'description' => $this->l('Add your TinyMCE api key (free) https://www.tiny.cloud/pricing/'), // description to display
                'tab' => 'Settings',
                'default' => 'no-api-key', // default value (Boolean)
            ],
        ];
    }

    /**
     * Register blocks into prettyblocks
     * register smartyblock
     */
    public function hookActionRegisterBlock($params)
    {
        $defaultsBlocks = [
            new ProductDescriptionBlock($this),
            new ProductDescriptionShortBlock($this),
            new CmsContentBlock($this),
            new CategoryDescriptionBlock($this),
        ];
        // https://preview.keenthemes.com/html/keen/docs/general/tiny-slider/overview
        $defaultsBlocks[] = new TinySlider($this);
        $defaultsBlocks[] = new CustomImage($this);

        return HelperBuilder::renderBlocks($defaultsBlocks);
    }
}
