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
    public $id_shop = 0;
    public $value = '';
    public $newValue = '';
    public $key = '';
    private $field = [];
    public $label = '';
    public $model = null;
    public $force_default_value = false;
    public $allow_html = true;
    public $context = 'front';


    public function __construct($block)
    {
        $this->block = $block;

        $this->setIdLang((int)$block['id_lang']);
        $this->setIdShop((int)$block['id_shop']);
    }

    private function getConfig()
    {
        $this->config = json_decode($this->block['config'], true);
    }

     /*
        |
        |--------------------------------------------------------------------------
        | set the context: 'front' or 'back' only.
        |--------------------------------------------------------------------------
        |
    */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function forceDefaultValue($value = true)
    {
        $this->force_default_value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->setValue();
        return $this;
    }

     /*
        |
        |--------------------------------------------------------------------------
        | if the field can contains html or not
        |--------------------------------------------------------------------------
        |
    */

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
    public function get()
    {

       
        // set value if exists
        if(isset($this->block['id_prettyblocks']))
        {
            $this->model = new PrettyBlocksModel((int)$this->block['id_prettyblocks'],$this->id_lang,$this->id_shop);
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
         // force default value
        if(isset($this->field['force_default_value']) && $this->field['force_default_value'] === true)
        {
            $this->force_default_value = true;   
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
        $this->get();
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

        $values = $this->getFormattedConfig();
        if(isset($values[$this->key]))
        {
            $this->value = $values[$this->key];
        }else{
            $this->value = $this->format();
        }

        $this->field['value'] = $this->value;   

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
        // $data = $this->getFormattedConfig();
        // foreach (Language::getLanguages() as $lang)
        // {
        //     $id_lang = (int) $lang['id_lang'];
        //     if($this->id_lang == $id_lang)
        //     {
        //         $data[$id_lang][$this->key] = $this->format();
        //     } else{
        //         $data[$id_lang][$this->key] = $data[$id_lang][$this->key] ?? '';
        //     }
        // }
        // return $data;

        $data = $this->getFormattedConfig();
        foreach (Language::getLanguages() as $lang)
        {
            $data[$this->key] = $this->format();
        }
        return $data;
    }

    public function setIdLang($id_lang)
    {
        $this->id_lang = (int)$id_lang;
        return $this;
    }

    public function setIdShop($id_shop)
    {
        $this->id_shop = (int)$id_shop;
        return $this;
    }


     /*
        |--------------------------------------------------------------------------
        | getFormattedConfig
        |--------------------------------------------------------------------------
        |
        | Return the json in config block database and decode it
        | format should be like this
        | [
        |   {field}: {value formatted}
        | ]
        ] 
        |
    */


    public function getFormattedConfig()
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
    
    

    /*
        |
        |--------------------------------------------------------------------------
        | Format the value
        |--------------------------------------------------------------------------
        |
    */
    public function format()
    {
        $method = 'formatField'.ucwords($this->type);
        if(method_exists($this, $method)) {
            return $this->{$method}();
        }
        return false;
    }

    /*
        |
        |--------------------------------------------------------------------------
        | Save the model
        |--------------------------------------------------------------------------
        |
    */

    public function save()
    {
        $json = $this->_setFormattedValue();
        $json = json_encode($json);
        $this->model->config = $json;
        if($this->model->save())
        {
            $this->_setNewValue($this->newValue);
        }
        return $this;
    }

    private function _setNewValue($newValue)
    {
        $this->value = $newValue;
        $this->field['value'] = $this->value;
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

    private function formatFieldColor()
    {
        return $this->formatFieldText();
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