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
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class PrettyBlocks extends Module implements WidgetInterface
{
    public $js_path;
    public $css_path;
    public $dev_ps = true;
    public $valid_types = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
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
        'displayBeforeBodyClosingTag',
        'displayHeader',
        'displayLeftColumn',
        'displayRightColumn',
        'actionDispatcher',
        'actionFrontControllerSetMedia',
        'actionFrontControllerSetVariables',
    ];

    public function __construct()
    {
        $this->name = 'prettyblocks';
        $this->tab = 'administration';
        $this->version = '1.1.2';
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

    public function __call(string $name, array $arguments)
    {
        if (method_exists(\PrestaSafe\PrettyBlocks\Module\Hook::class, $name)) {

            if (substr($name, 0, 4) === 'hook') {

                return \PrestaSafe\PrettyBlocks\Module\Hook::execute($name, $this, $arguments[0] ?? null);
            }
        }
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
            `name` varchar(255) DEFAULT NULL,   
            `zone_name` varchar(255) DEFAULT NULL,
            `position` int(11) DEFAULT 0,
            `date_add` datetime DEFAULT NULL,
            `date_upd` datetime DEFAULT NULL,
            PRIMARY KEY (`id_prettyblocks`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $db[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'prettyblocks_lang` (
            `id_prettyblocks` int(11) unsigned NOT NULL,
            `state` longtext NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            PRIMARY KEY (`id_prettyblocks`,`id_shop`,`id_lang`),
            KEY `id_lang` (`id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $isOk = true;
        foreach ($db as $sql) {
            $isOk &= Db::getInstance()->execute($sql);
        }

        return $isOk;
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

        $isOk = true;
        foreach ($db as $sql) {
            $isOk &= Db::getInstance()->execute($sql);
        }

        return $isOk;
    }

    public function getContent()
    {
        $domain = Tools::getShopDomainSsl(true);
        $symfonyUrl = $domain . Link::getUrlSmarty([
            'entity' => 'sf',
            'route' => 'admin_prettyblocks',
        ]);

        return Tools::redirect($symfonyUrl);
    }

    private function loadDefault()
    {
        return Configuration::updateValue('_PRETTYBLOCKS_TOKEN_', Tools::passwdGen(25));
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

    public static function renderZone($params)
    {
        $zone_name = $params['zone_name'];

        if (empty($zone_name)) {
            return false;
        }

        $context = Context::getContext();
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        $blocks = PrettyBlocksModel::getInstanceByZone($zone_name, 'front', $id_lang, $id_shop);

        $context->smarty->assign([
            'zone_name' => $zone_name,
            'blocks' => $blocks,
        ]);

        return $context->smarty->fetch('module:prettyblocks/views/templates/front/zone.tpl');
    }
}
