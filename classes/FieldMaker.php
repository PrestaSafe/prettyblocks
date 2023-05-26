<?php

use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;
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
class FieldMaker{

    private $block;
    public $type = 'text';
    public $config = [];
    public $id_lang = 0;
    public $id_shop = 0;
    public $value = '';
    private $formattedValue = '';
    public $newValue = '';
    public $key = '';
    private $field = [];
    public $label = '';
    public $model = null;
    public $force_default_value = false;
    public $allow_html = true;
    public $context = 'front';
    public $mode = 'config';

    public function __construct($block)
    {
        $this->block = $block;

        $this->setIdLang((int)$block['id_lang']);
        $this->setIdShop((int)$block['id_shop']);
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
     * @return Any
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getFormattedValue()
    {
        return $this->formattedValue;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /** 
     * Set all essential Data
     */
    public function get()
    {

        // set value if exists
        if(is_null($this->model) && isset($this->block['id_prettyblocks']))
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


        $this->setValues();
        return $this;
    }
    /*
        |
        |--------------------------------------------------------------------------
        |set value if alrealy exist
        |--------------------------------------------------------------------------
        |
    */
    public function getFieldData($data, $shouldReturn = false)
    {
        return $this->field[$data] ?? $shouldReturn;
    }

    public function setValues()
    {
        $values = $this->getFormattedConfig();
        if(isset($values[$this->key]) && $this->newValue === '')
        {
            $this->value = $values[$this->key];
        } else {
            $this->value = $this->format();
        }
        $this->formattedValue = $this->formatForFront();
        $this->field['value'] = $this->formattedValue;   

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
    

    private function _setFormattedValue()
    {
        $data = $this->getFormattedConfig();
        $data[$this->key] = $this->format();
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
        $jsonConfig = $this->model->config;    
        
        if(!is_null($jsonConfig) && !Validate::isJson($jsonConfig))
        {
            return $value;
        }
        $json = json_decode($jsonConfig, true);
        // if(!in_array($this->key, $json))
        // {
        //     unset($json[$this->key]);
        // }

        return $json;
    }
    
    

    /*
        |
        |--------------------------------------------------------------------------
        | Format the value for PrettyBlocks backend
        |--------------------------------------------------------------------------
        |
    */
    public function format()
    {
        $method = 'formatField'.ucwords(str_replace('_','',$this->type));
        if(method_exists($this, $method)) {
            return $this->{$method}();
        }
        return false;
    }

    public function formatForFront() 
    {
        $method = 'formatField'.ucwords(str_replace('_','',$this->type)).'ForFront';
        if(method_exists($this, $method)) {
            return $this->{$method}();
        }
        return $this->format();
    }
    /*
        |
        |--------------------------------------------------------------------------
        | Save in the model
        |--------------------------------------------------------------------------
        |
    */

    public function save()
    {
        $json = $this->_setFormattedValue();
        $json = json_encode($json, true);
        $this->model->config = $json;
        if($this->model->save())
        {
            $this->_assignValues($this->newValue);
        }
        return $this;
    }

    private function _assignValues($newValue)
    {
        $this->value = $newValue;
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
        // if value exists in DB and newValue is empty
        if($this->value !== '' && $this->newValue === '')
        {
            return $this->secureTextEntry($this->value);
        }       
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && $this->newValue === '')
        {
            return $this->secureTextEntry($this->field['default']);
        }

        return $this->secureTextEntry($this->newValue);
        
    }

    private function formatFieldColor()
    {
        return $this->formatFieldText();
    }

    private function formatFieldTextarea()
    {
        return $this->formatFieldText();
    }

    private function formatFieldFileupload()
    {

        if($this->force_default_value && $this->newValue == '')
        {
            return $this->field['default'] ? $this->secureFileUploadEntry($this->field['default']) : '';
        }
        if(is_array($this->newValue) && isset($this->newValue['url']))
        {
            return $this->secureFileUploadEntry($this->newValue);
        }
    }

    private function formatFieldEditor()
    {
        $this->allow_html = true;
        return $this->formatFieldText();
    }
    

    private function formatFieldCheckbox()
    {    
        // if value exists in DB and newValue is empty

        if($this->value !== '' && $this->newValue === '')
        {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN) ?? false;
        }       
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && $this->newValue === '')
        {
            return filter_var($this->field['default'], FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        return filter_var($this->newValue, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * return the value for PrettyBlocks (backend)
     */
    private function formatFieldRadioGroup()
    {
        if(!is_array($this->field['choices']))
        {
            return '';
        }
        // if value exists in DB and newValue is empty
        if($this->value !== '' && empty($this->newValue) && isset($this->field['choices'][$this->value]))
        {
            return pSQL($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && $this->newValue === '')
        {
            if(is_array($this->field['choices']) 
            && isset($this->field['default'])
            && isset($this->field['choices'][$this->field['default']]) )
            {
                return pSQL($this->field['default']);
            }

            // get default value
            if(is_array($this->field['choices'] && !empty($this->field['choices'])))
            {
                reset($this->field['choices']);
                $firstKey = key($this->field['choices']);
                return pSQL($firstKey);
            }
        }
        // if value doesn't exists in DB and new value is set and force default value is false
        if( is_array($this->field['choices']) && isset($this->field['choices'][$this->newValue]))
        {
            return pSQL($this->newValue);
        }
        // if no matches. 
        return '';
    }

    private function formatFieldRadioGroupForFront()
    {

        if(!is_array($this->field['choices']))
        {
            return '';
        }
        // if value exists in DB and newValue is empty
        if($this->value !== '' && empty($this->newValue) && isset($this->field['choices'][$this->value]))
        {
            if($this->allow_html)
            {
                return pSQL(Tools::purifyHTML($this->field['choices'][$this->value]));
            }
            return pSQL($this->field['choices'][$this->value]);
        }
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && $this->newValue == '')
        {
            if(is_array($this->field['choices']) 
            && isset($this->field['default'])
            && isset($this->field['choices'][$this->field['default']]) )
            {
                return $this->field['choices'][$this->field['default']];
            }

            // get default value
            if(is_array($this->field['choices'] && !empty($this->field['choices'])))
            {
                reset($this->field['choices']);
                $firstKey = key($this->field['choices']);
                return pSQL($this->field['choices'][$firstKey]);
            }
        }
        // if value doesn't exists in DB and new value is set and force default value is false
        if( is_array($this->field['choices']) && isset($this->field['choices'][$this->newValue]))
        {
            if($this->allow_html)
            {
                return pSQL(Tools::purifyHTML($this->field['choices'][$this->newValue]));
            }
            return pSQL($this->field['choices'][$this->newValue]);
        }
        // if no matches. 
        return '';
    }   
    private function formatFieldSelectForFront()
    {
        return $this->formatFieldRadioGroupForFront();
    }

    private function formatFieldSelect()
    {
        return $this->formatFieldRadioGroup();
    }

    /**
     * @todo CHECK RETURN OBJECT PRESENTER FOR FRONT
     */
    private function formatFieldSelectorForFront()
    {
        // if value exists in DB && newValue is empty
        if($this->value !== '' && empty($this->newValue) && is_array($this->value) && isset($this->value['show']['id']))
        {
            $idCollection = (int)$this->value['show']['id'];
            return $this->_getCollection($idCollection, $this->field['collection']);
        }
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && $this->newValue == '')
        {
            $idCollection = (int)$this->field['default']['show']['id'];
            return $this->_getCollection($idCollection, $this->field['collection']);
        }

        // if value doesn't exists in DB and new value is set and force default value is false
        if( is_array($this->newValue) && isset($this->newValue['show']['id']))
        {
            $idCollection = (int)$this->newValue['show']['id'];
            return $this->_getCollection($idCollection, $this->field['collection']);
        }
        // if no matches. 
        return false;
    }

    private function formatFieldSelector()
    {
        // if value exists in DB && newValue is empty
        if($this->value !== '' && empty($this->newValue) && is_array($this->value) && isset($this->value['show']['id']))
        {
            return $this->secureCollectionEnters($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && $this->newValue == '')
        {
            return $this->secureCollectionEnters($this->field['default']);
        }

        // if value doesn't exists in DB and new value is set and force default value is false
        if( is_array($this->newValue) && isset($this->newValue['show']['id']))
        {
            return $this->secureCollectionEnters($this->newValue);
        }
        // if no matches. 
        return false;
    }

    private function _getCollection($id, $collectionName, $primaryField = null)
    {
        $c = new \PrestaShopCollection($collectionName, $this->id_lang);
        $primary = $primaryField ?? 'id_' . \Tools::strtolower($collectionName);

        $object = $c->where($primary, '=', (int) $id)->getFirst();
        if (!Validate::isLoadedObject($object)) {
            return false;
        }
        $objectPresenter = new ObjectPresenter();
        return $objectPresenter->present($object);
    }

    private function secureCollectionEnters($array)
    {
        $secure = [];
        $secure['show'] = [
            'id' => (int)$array['show']['id'],
            'primary' => (int)$array['show']['primary'],
            'name' => pSQL($array['show']['name']),
            'formatted' => pSQL($array['show']['formatted']),
        ];
        return $secure;

    }

    private function secureTextEntry($string)
    {
        if($this->allow_html)
        {
            return Tools::purifyHTML($string);
        }
        return pSQL(stripslashes($string));
    }

    private function secureFileUploadEntry($array)
    {
        $secure = [];
        $secure['url'] = pSQL($array['url']);
        return $secure;
    }


}