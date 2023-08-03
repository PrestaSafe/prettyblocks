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

use PrestaSafe\PrettyBlocks\Core\PrettyBlocksStatesField;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrettyBlocksAjaxModuleFrontController extends ModuleFrontController
{
    private $ajax_token;
    protected $translator;

    public function __construct()
    {
        $this->ajax_token = Configuration::get('_PRETTYBLOCKS_TOKEN_');
        $this->translator = Context::getContext()->getTranslator();
        parent::__construct();
    }

    public function init()
    {
        if (empty($_POST)) {
            $_POST = json_decode(Tools::file_get_contents('php://input'), true);
            if (!is_array($_POST)) {
                $_POST = [];
            }
        }
        if (empty($this->ajax_token) || Tools::getValue('ajax_token') !== $this->ajax_token) {
            header('HTTP/1.1 401 Unauthorized');
            exit('Wrong token');
        }
        $this->ajax = $this->isAjax();

        parent::init();
    }

    /**
     * Returns if the current request is an AJAX request.
     *
     * @return bool
     */
    private function isAjax()
    {
        // Usage of ajax parameter is deprecated
        $isAjax = Tools::getValue('ajax') || Tools::isSubmit('ajax');

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $isAjax = $isAjax || preg_match(
                '#\bapplication/json\b#',
                $_SERVER['HTTP_ACCEPT']
            );
        }

        return $isAjax;
    }

    /**
     * insert block on zone
     *
     * @return string
     */
    public function displayAjaxinsertBlock()
    {
        $code = pSQL(Tools::getValue('code'));
        $zone_name = pSQL(Tools::getValue('zone_name'));
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = \PrettyBlocksModel::registerBlockToZone($zone_name, $code, $id_lang, $id_shop);

        exit(json_encode([
            'state' => $state,
            'errors' => 'No action found',
        ]));
    }

    public function displayAjaxgetBlockConfig()
    {
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $id_block = (int) Tools::getValue('id_prettyblocks');
        $state = new PrettyBlocksModel($id_block, $id_lang);
        $block = $state->mergeStateWithFields();

        exit(json_encode([
            'state' => $block,
            'config' => $block['settings_formatted'],
        ]));
    }

    // a tester
    public function displayAjaxgetState()
    {
        $state = new PrettyBlocksModel((int) Tools::getValue('id_prettyblocks'));
        $block = $state->mergeStateWithFields();
        exit(json_encode([
            'state' => $block,
            'state_db' => $block['repeater_db'],
        ]));
    }

    public function displayAjaxgetSubState()
    {
        return $this->displayAjaxgetState();
    }
    // OK

    // remove sub element OK
    public function displayAjaxremoveSubState()
    {
        $formattedID = pSQL(Tools::getValue('formattedID'));
        $ids = explode('-', $formattedID);
        $id_block = $ids[0];
        $substate_key = $ids[1];
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $block = $state->mergeStateWithFields();
        $state_db = json_decode($state->state, true);

        if (isset($state_db[$substate_key])) {
            unset($state_db[$substate_key]);
        }
        $encoded = json_encode($state_db);
        $state->state = $encoded;
        if ($state->save()) {
            exit(json_encode([
                'success' => true,
                'state' => $encoded,
            ]));
        }
    }

    // remove element
    public function displayAjaxremoveState()
    {
        $id_prettyblocks = (int) Tools::getValue('id_prettyblocks');
        $block = new PrettyBlocksModel($id_prettyblocks);
        if ($block->delete()) {
            exit(json_encode([
                'success' => true,
                'message' => $this->translator->trans('Block removed with success', [], 'Modules.Prettyblocks.Admin'),
            ]));
        }
    }

    // for pushing an empty element repeater
    public function displayAjaxgetEmptyState()
    {
        $success = true;
        $id_block = (int) Tools::getValue('id_prettyblocks');
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);

        $block = $state->mergeStateWithFields();
        $state_to_push = $block['state_to_push'];

        if (!isset($block['state_to_push'])) {
            $success = false;
            $state_to_push = [];
            exit(json_encode([
                'state_to_push' => $state_to_push,
                'success' => $success,
            ]));
        }
        $state_db = json_decode($state->state, true);
        $maxKey = 1;
        if (count($state_db) > 0) {
            foreach ($state_db as $key => $value) {
                if ($key >= $maxKey) {
                    $maxKey = $key;
                }
            }
        }

        $state_db[$maxKey + 1] = (object) $state_to_push;
        $encoded = json_encode($state_db);
        $state->state = $encoded;
        if ($state->save()) {
            exit(json_encode([
                'success' => $success,
                'to_push' => $state_to_push,
            ]));
        }
    }

    public function displayAjaxupdateStatePosition()
    {
        $items = Tools::getValue('items');

        $item0 = $items[0];
        $id_block = (int) $item0['id_prettyblocks'];
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $state_db = json_decode($state->state, true);
        $keyPositions = [];
        foreach ($items as $item) {
            $itemDecoded = $item;
            $ids = explode('-', $itemDecoded['id']);
            $substate_key = $ids[1];
            $keyPositions[$substate_key] = $state_db[(int) $substate_key];
        }

        $state->state = json_encode($keyPositions);
        $state->save();
        exit(json_encode([
            'state' => $items,
        ]));
    }

    public function displayAjaxloadBlockById()
    {
        $id_block = (int) Tools::getValue('id_prettyblocks');
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $block = $state->mergeStateWithFields();

        return exit(json_encode($block, true));
    }

    public function displayAjaxupdateStateParentPosition()
    {
        $items = Tools::getValue('items');
        $i = 1;
        $position = [];
        foreach ($items as $item) {
            $item = (object) $item;
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'prettyblocks` SET position=' . $i . ' WHERE id_prettyblocks = ' . (int) $item->id_prettyblocks;
            $position[$item->id_prettyblocks] = $position;
            Db::getInstance()->execute($sql);
            ++$i;
        }
        exit(json_encode([
            'success' => true,
            'message' => $this->translator->trans('Updated with success', [], 'Modules.Prettyblocks.Admin'),
        ]));
    }

    public function displayAjaxupdateThemeSettings()
    {
        $stateRequest = Tools::getValue('stateRequest');
        PrettyBlocksModel::updateThemeSettings($stateRequest);
        exit(json_encode([
            'success' => true,
            'saved' => true,
            'message' => $this->translator->trans('Updated with success', [], 'Modules.Prettyblocks.Admin'),
        ], true));
    }

    public function displayAjaxGetStates()
    {
        $zone = pSQL(Tools::getValue('zone'));
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');

        exit(json_encode(
            [
                'blocks' => PrettyBlocksModel::getInstanceByZone($zone, 'back', $id_lang, $id_shop),
                'id_lang' => $id_lang,
                'id_shop' => $id_shop,
            ]
        ));
    }

    public function displayAjaxupdateBlockConfig()
    {
        $id_block = (int) Tools::getValue('id_prettyblocks');
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $stateRequest = Tools::getValue('state');

        if ($state->updateConfig($stateRequest)) {
            exit(json_encode([
                'success' => true,
                'saved' => true,
                'state' => $stateRequest,
                'message' => $this->translator->trans('Updated with success', [], 'Modules.Prettyblocks.Admin'),
            ]));
        }
    }

    public function displayAjaxUpdateState()
    {
        $id_block = (int) Tools::getValue('id_prettyblocks');
        $substate_key = (int) Tools::getValue('subSelected');
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);

        $stateRequest = Tools::getValue('state');
        $formattedState = json_decode($stateRequest, true);

        $state_decoded = json_decode($state->state, true);
        $state_decoded[$substate_key] = $formattedState;

        $state->state = json_encode($state_decoded);

        if ($state->save()) {
            exit(json_encode([
                'success' => true,
                'saved' => true,
                'state' => $stateRequest,
                'message' => $this->translator->trans('Updated with success', [], 'Modules.Prettyblocks.Admin'),
            ]));
        }
    }

    public function displayAjaxGetBlockRender()
    {
        $id_block = (int) Tools::getValue('id_prettyblocks');
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $block = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $html = $this->module->renderWidget(null, [
            'action' => 'GetBlockRender',
            'data' => $block->mergeStateWithFields(),
        ]);

        exit(json_encode(
            [
                'html' => $html,
            ]
        ));
    }

    public function displayAjaxBlockRender()
    {
        // for drag n drop
        // not used for now
        $html = '';
        $block_name = Tools::getValue('block');
        $block = $this->module->registerBlockToZone('displayHome', $block_name);
        $html = $this->module->renderWidget(null, [
            'block' => $block_name,
            'instance' => $block,
        ]);

        exit(json_encode(
            [
                'html' => $html,
                'block' => Tools::getValue('block'),
            ]
        ));
    }

    /**
     * @return json
     */
    public function displayAjaxupdateTitleComponent()
    {
        $id_lang = (int) Tools::getValue('ctx_id_lang');
        $id_shop = (int) Tools::getValue('ctx_id_shop');
        $id_prettyblocks = (int) Tools::getValue('id_prettyblocks');
        $element = Tools::getValue('element');
       
        $model = new \PrettyBlocksModel($id_prettyblocks, $id_lang, $id_shop);
        $model->mergeStateWithFields();
        if(Tools::getIsset('index'))
        {
            // save state
            $index = (int)Tools::getValue('index');
            dump(Tools::getAllValues());
            if($model->saveStateField((int)$index, pSQL(Tools::getValue('field')), $element))
            {
                $success = true;
            }
            
        } else{
            if($model->saveConfigField(pSQL(Tools::getValue('field')), $element)){
                $success = true;
            }
        }
        exit(json_encode([
            'success' => $success,
            'message' => $this->translator->trans('Title updated with success', [], 'Modules.Prettyblocks.Admin'),
            'errors' => 'No action found',
        ]));
    }
}
