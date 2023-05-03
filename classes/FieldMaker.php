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
/**
 * Please don't judge me
 * it will be refactoring soon.
 */
class FieldMaker{

    private array $block;
    public $type = 'text';
    public $config = [];
    public $id_lang = 0;
    public $value = '';
    public $newValue = '';
    public $key = '';
    private $field = [];
    public $label = '';
    public $model = null;
    public $force_default_value = false;
    public $allow_html = true;


    public function __construct($block)
    {
        $this->block = $block;
    }

    private function getConfig()
    {
        $this->config = json_decode($this->block['config'], true);
    }

    

    public function forceDefaultValue($value = true)
    {
        $this->force_default_value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->setValue();
        return $this;
    }

    public function allowHtml($value)
    {
        $this->allow_html = (bool)$value;
        return $this;
    }

    /** 
     * @param String $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /** 
     * Set all essential Data
     */
    public function shake()
    {

       
        // set value if exists
        if(isset($this->block['id_prettyblocks']))
        {
            // IMPORTANT @TODO SET ID LANG ID SHOP
            // !!!!!!!
            // !!!!!!!
            $this->model = new PrettyBlocksModel((int)$this->block['id_prettyblocks'],1,1);
            $this->config = json_decode($this->model->config, true);
            $this->id_lang = (int)$this->model->id_lang;
        }
        $this->_setField();

         // set label
         if(isset($this->field['label']))
         {
             $this->label = $this->field['label'];   
         }
         // set type 
         if(isset($this->field['type']))
         {
             $this->type = $this->field['type'];   
         }
         // set id_lang
         if(isset($this->field['id_lang']))
         {
             $this->id_lang = $this->field['id_lang'];   
         }

        $this->setValue();
        return $this;
    }

    /**
     * @param String $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        $this->shake();
        $this->_setField();
        return $this;
    }
    
    private function _setField()
    {   

        if(isset($this->block['config']['fields'][$this->key]))
        {
            $this->field = $this->block['config']['fields'][$this->key];
        }
        return $this;
    }
    /*
        |
        |--------------------------------------------------------------------------
        |set value if alrealy exist
        |--------------------------------------------------------------------------
        |
    */
    public function setValue()
    {
        $values = $this->_getFormattedValue();
        if(isset($values[$this->id_lang][$this->key]))
        {
            $this->value = $values[$this->id_lang][$this->key];
        }else{
            $this->value = $this->format();
        }
        return $this;
    }

    /** 
     * @param any $value
     */
    public function setNewValue($value)
    {
        $this->newValue = $value;
        return $this;
    }

    private function _setFormattedValue()
    {
        $data = $this->_getFormattedValue();
        foreach (Language::getLanguages() as $lang)
        {
            $id_lang = (int) $lang['id_lang'];
            if($this->id_lang == $id_lang)
            {
                $data[$id_lang][$this->key] = $this->format();
            } else{
                $data[$id_lang][$this->key] = $data[$id_lang][$this->key] ?? '';
            }
        }
        return $data;
    }

    public function setIdLang($id_lang)
    {
        $this->id_lang = $id_lang;
        return $this;
    }


     /*
        |--------------------------------------------------------------------------
        | _getFormattedValue
        |--------------------------------------------------------------------------
        |
        | Return the json in config block database and decode it
        | format should be like this
        | [
        |   1: 'Banner title',
        |   2: 'Banner title 2'
        | ]
        ] 
        |
    */


    private function _getFormattedValue()
    {
        $value = [];
        // get Json value formatted in database
        $json = $this->model->config;    
        if(!is_null($json) && !Validate::isJson($json))
        {
            return $value;
        }
        $json = json_decode($json, true);
        return $json;
    }
    

    /** 
     * @param int $id_lang
     */
    public function format()
    {
        $method = 'formatField'.ucwords($this->type);
        if(method_exists($this, $method)) {
            return $this->{$method}();
        }
        return false;
    }

    public function save()
    {
        $json = $this->_setFormattedValue();
        $json = json_encode($json);
        $this->model->config = $json;
        if($this->model->save())
        {
            $this->value = $this->newValue;
        }
        return $this;
    }


    /*
        |
        |--------------------------------------------------------------------------
        | Formatted Method
        |--------------------------------------------------------------------------
        |
    */

    private function formatFieldText()
    {
        if($this->force_default_value && $this->newValue == '')
        {
            return $this->field['default'] ?? '';
        }
        return stripslashes((string)$this->newValue);
    }
    
    // private function formatFieldBoxes()
    // {
        
        //     $default_value = ($data['default']) ? filter_var($data['default'], FILTER_VALIDATE_BOOLEAN) : false;
        //     $key = self::getKey($name, $block);
        //     $id_shop = ($block && $block['id_shop']) ? (int) $block['id_shop'] : (int) Context::getContext()->shop->id;
        //     if (!Configuration::hasKey($key)) {
            //         return $default_value;
            //     }
            //     $res = filter_var(Configuration::get($key, null, null, $id_shop), FILTER_VALIDATE_BOOLEAN);
            
            //     return $res;
            // }
            
    private function formatFieldCheckbox()
    {           
        if($this->force_default_value && $this->newValue == '')
        {
            return filter_var($this->field['default'], FILTER_VALIDATE_BOOLEAN) ?? false;
        }
        return filter_var($this->newValue, FILTER_VALIDATE_BOOLEAN);
    }

}