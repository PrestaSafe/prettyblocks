<?php
if (!defined('_PS_VERSION_')) {
    exit;
}



class PrettyBlocksAjaxModuleFrontController extends ModuleFrontController
{
    private $ajax_token;
    public function __construct()
    {
        $this->ajax_token = Configuration::get('_PRETTYBLOCKS_TOKEN_', Tools::passwdGen(25));
        parent::__construct();
    }
    public function init()
    {
      
      parent::init();
    } 

    public function displayAjax()
    {

    }

    public function initContent()
    {
        
        if (empty($_POST)) {
            $_POST = json_decode(file_get_contents("php://input"),true);
        }
        if($this->ajax && !empty($this->ajax_token) && (!Tools::getIsset('ajax_token') || Tools::getValue('ajax_token') !== $this->ajax_token))
        {
            throw new Exception('Wrong ajax token !');
        }
        parent::initContent();
    }

    public function displayAjaxgetBlockConfig() {
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $id_block = (int)Tools::getValue('id_prettyblocks');
        $state = new PrettyBlocksModel($id_block,$id_lang,$id_shop);
        $block = $state->mergeStateWithFields();

        die(json_encode([
            'state' => $block,
            'config' => $block['settings_formatted']
        ]));
    }

    // a tester
    public function displayAjaxgetState(){
        
        $state = new PrettyBlocksModel((int)Tools::getValue('id_prettyblocks'));
        $block = $state->mergeStateWithFields();
        die(json_encode([
            'state' => $block,
            'state_db' => $block['repeater_db']
        ]));
    }
    public function displayAjaxgetSubState() {
        return $this->displayAjaxgetState();
    }
    // OK 


    // remove sub element OK
    public function displayAjaxremoveSubState() {
        $formattedID = (string)Tools::getValue('formattedID');
        $ids = explode('-', $formattedID);
        $id_block = $ids[0];
        $substate_key = $ids[1];
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $block = $state->mergeStateWithFields();
        $state_db = json_decode($state->state, true);

        if (isset($state_db[$substate_key])) {
            unset($state_db[$substate_key]);
        }
        $encoded = json_encode($state_db);
        $state->state = $encoded;
        if ($state->save()) {
            die(json_encode([
                'success' => true,
                'state' => $encoded,
            ]));
        }
    }

    // remove element
    public function displayAjaxremoveState() {
        $id_prettyblocks = (string)Tools::getValue('id_prettyblocks');
        $block = new PrettyBlocksModel($id_prettyblocks);
        if ($block->delete()) {
            die(json_encode([
                'success' => true,
            ]));
        }
    }

     // for pushing an empty element repeater
     public function displayAjaxgetEmptyState() {
        $success = true;
        $id_block = (int)Tools::getValue('id_prettyblocks');
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);


        $block = $state->mergeStateWithFields();
        $state_to_push = $block['state_to_push'];

        if (!isset($block['state_to_push'])) {
            $success = false;
            $state_to_push = [];
            die(json_encode([
                'state_to_push' => $state_to_push,
                'success' => $success
            ]));
        }
        $state_db = json_decode($state->state, true);
        $maxKey = 1; 
        if(count($state_db) > 0)
        {
            foreach($state_db as $key => $value)
            {
                if($key >= $maxKey)
                {
                    $maxKey = $key;
                }
            }
        }
        
        
        $state_db[$maxKey+1] = (object)$state_to_push;
        $encoded = json_encode($state_db);
        $state->state = $encoded;
        if ($state->save()) {
            die(json_encode([
                'success' => $success,
                'to_push' => $state_to_push,
            ]));
        }
    }

    public function displayAjaxupdateStatePosition()
    {
        $items = Tools::getValue('items');
        $item0 = $items[0];
        $id_block = (int)$item0['id_prettyblocks'];
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $state_db = json_decode($state->state, true);
        $keyPositions = [];
        foreach($items as $item)
        {
            $itemDecoded = $item;
            $ids = explode('-', $itemDecoded['id']);
            $substate_key = $ids[1];
            $keyPositions[$substate_key] = $state_db[(int)$substate_key];
        }
        

        $state->state = json_encode($keyPositions);
        $state->save();
        die(json_encode([
            'state' => $items,
            // 'errors' => $action
        ]));
    }

    public function displayAjaxloadBlockById()
    {
        $id_block = (int)Tools::getValue('id_prettyblocks');
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $block = $state->mergeStateWithFields();

        return die(json_encode($block,true));
    }

    public function displayAjaxupdateStateParentPosition()
    {
        $items = Tools::getValue('items');
        $i = 1;
        $position = [];
        foreach($items as $item)
        {
            $item = (object)$item;
            $sql = 'UPDATE `'._DB_PREFIX_.'prettyblocks` SET position='.$i.' WHERE id_prettyblocks = '.(int)pSQL($item->id_prettyblocks);
            $position[$item->id_prettyblocks] = $position;
            Db::getInstance()->execute($sql);
            $i++;
        }
    }

    public function displayAjaxupdateThemeSettings()
    {
        $stateRequest = Tools::getValue('stateRequest');
        PrettyBlocksModel::updateThemeSettings($stateRequest);
        die(json_encode([
            'success' => true,
            'saved' => true,
            'message' => 'Settings updated with success !'
        ], true));
    }

    public function displayAjaxGetStates()
    {
        $zone = pSQL(Tools::getValue('zone'));
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        die(json_encode(
            [
                'blocks' => PrettyBlocksModel::getInstanceByZone($zone, 'back', $id_lang, $id_shop),
                'id_lang' => $id_lang,
                'id_shop' => $id_shop
            ])
        );
    }
    public function displayAjaxupdateBlockConfig()
    {

        $id_block = (int)Tools::getValue('id_prettyblocks');
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $state = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $stateRequest = Tools::getValue('state');
        $state->updateConfig($stateRequest);
    
        if ( $state->updateConfig($stateRequest)) {
            die(json_encode([
                'success' => true,
                'saved' => true,
                'state' => $stateRequest,
                'message' => 'Updated with success'
            ]));
        }
    }

    public function displayAjaxUpdateState()
    {

            $id_block = (int)Tools::getValue('id_prettyblocks');
            $substate_key = (int)Tools::getValue('subSelected');
            $id_lang = (int)Tools::getValue('ctx_id_lang');
            $id_shop = (int)Tools::getValue('ctx_id_shop');
            $state = new PrettyBlocksModel($id_block,$id_lang,$id_shop);
        
            $stateRequest = Tools::getValue('state');
            $formattedState = json_decode($stateRequest, true);


            $state_decoded = json_decode($state->state, true);
            $state_decoded[$substate_key] = $formattedState;

            $state->state = json_encode($state_decoded);

            if ($state->save()) {
                die(json_encode([
                    'success' => true,
                    'saved' => true,
                    'state' => $stateRequest,
                    'message' => 'Updated with success'
                ]));
            }

    }

    public function displayAjaxGetBlockRender()
    {
  
        $id_block = (int)Tools::getValue('id_prettyblocks');
        $id_lang = (int)Tools::getValue('ctx_id_lang');
        $id_shop = (int)Tools::getValue('ctx_id_shop');
        $block = new PrettyBlocksModel($id_block, $id_lang, $id_shop);
        $module = Module::getInstanceByName('prettyblocks');
        $html = $module->renderWidget(null,[
            'action' => 'GetBlockRender',
            'data' => $block->mergeStateWithFields()
        ]); 


        die(json_encode(
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
        $module = Module::getInstanceByName('prettyblocks');
        $block_name = Tools::getValue('block');
        $block = $module->registerBlockToZone('displayHome',$block_name);
        $html = $module->renderWidget(null,[
            'block' => $block_name,
            'instance' => $block
        ]); 

        die(json_encode(
            [
                'html' => $html,
                'block' => Tools::getValue('block')
            ]
        ));
        
    }


}
