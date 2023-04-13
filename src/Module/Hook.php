<?php

namespace PrestaSafe\PrettyBlocks\Module;

use CMS;
use Context;
use Media;
use PrestaSafe\PrettyBlocks\Handler\ToolbarCheckerHandler;
use PrettyBlocks;
use PrettyBlocksModel;
class Hook
{

    private $hook_name;
    private $context;
    private $params;
    private $module;
    private static $instance = null;
    private bool $displayToolbar;

    /**
     * Hook constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param string $hook_name
     * @param PrettyBlocks $module
     * @param array $params
     * @return
     */
    public static function execute(string $hook_name, PrettyBlocks $module, array $params)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        self::$instance->hook_name = $hook_name;
        self::$instance->module = $module;
        self::$instance->params = $params;
        self::$instance->context = Context::getContext();
        self::$instance->displayToolbar = ToolbarCheckerHandler::canDisplay();
        return self::$instance->$hook_name($params);
    }


    public function hookDisplayHeader($params)
    {
        if ($this->displayToolbar) {
            $this->context->controller->registerStylesheet('prettyblocks_toolbar_css',
                'modules/' . $this->module->name . '/views/css/toolbar.css',
                ['media' => 'all', 'priority' => 150]);
            $this->context->controller->registerJavascript('prettyblocks_toolbar_js',
                'modules/' . $this->module->name . '/views/js/toolbar.js',
                ['position' => 'bottom', 'priority' => 150]);
        }
    }

    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if ($this->displayToolbar) {

            $this->context->smarty->assign([
                'prettyblocks' => [
                    'cms'     => CMS::getLinks((int)$this->context->language->id) ?? [],
                    'imgDir'  => _MODULE_DIR_ . $this->module->name . '/views/images/'
                ]
            ]);

            return $this->context->smarty->fetch('module:'. $this->module->name . '/views/templates/front/toolbar.tpl');
        }

        return false;
    }


    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'prettyblocksutility',
            'modules/' . $this->module->name . '/views/css/utility.css',
            [
                'media' => 'all',
                'priority' => 100,
            ]
        );
    }

    public function hookActionFrontControllerSetVariables()
    {
        Media::addJsDef([
            'toolbarSearchUrl' => $this->context->link->getModuleLink($this->module->name, 'toolbar', ['ajax' => 1])
        ]);

        return [
            // 'ajax_builder_url' => $this->context->link->getModuleLink($this->name,'ajax'),
            'theme_settings' => PrettyBlocksModel::getThemeSettings(false, 'front'),
            'id_shop' => (int) $this->context->shop->id,
        ];
    }

    /**
     * Hook dispatcher for registering smarty function
     */
    public function hookActionDispatcher()
    {
        /** @deprecated {magic_zone} is deprecated since v1.1.0. Use {prettyblocks_zone} instead. */
        $this->context->smarty->registerPlugin('function', 'magic_zone', [PrettyBlocks::class, 'renderZone']);
        $this->context->smarty->registerPlugin('function', 'prettyblocks_zone', [PrettyBlocks::class, 'renderZone']);
    }
}

