<?php

use PrestaSafe\PrettyBlocks\DataProvider\Toolbar\ToolbarCategoryDataProvider;
use PrestaSafe\PrettyBlocks\DataProvider\Toolbar\ToolbarCMSDataProvider;
use PrestaSafe\PrettyBlocks\DataProvider\Toolbar\ToolbarProductDataProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrettyBlocksToolbarModuleFrontController extends ModuleFrontController
{
    public function displayAjax()
    {
        $term = Tools::getValue('terms', '');
        $type = Tools::getValue('type', '');
        $data = [
            'success' => false,
            'data'    => []
        ];

        if (empty($term) || empty($type)) {
            $this->ajaxRender(json_encode($data));
        }

        switch ($type) {
            case 'product':
                $results = ToolbarProductDataProvider::getByTerms($term);
                break;
            case 'category':
                $results = ToolbarCategoryDataProvider::getByTerms($term);
                break;
            case 'cms':
                $results = ToolbarCMSDataProvider::getByTerms($term);
                break;
            default:
                $results = [];
                break;
        }

        $this->context->smarty->assign([
            'results' => $results
        ]);

        $data = [
            'data'    => $this->module->display($this->module->name, '/views/templates/front/toolbar-search-results.tpl'),
            'success' => true
        ];

        $this->ajaxRender(json_encode($data));
    }
}
