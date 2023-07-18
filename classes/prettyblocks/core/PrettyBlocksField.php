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

namespace PrestaSafe\PrettyBlocks\Core;

use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;

class PrettyBlocksField
{
    private $block;
    private $type = 'text';
    private $config = [];
    private $id_lang = 0;
    private $id_shop = 0;
    private $value;
    private $formattedValue = '';
    private $newValue;
    private $key = '';
    private $field = [];
    private $label = '';
    private $model;
    private $force_default_value = false;
    private $allow_html = true;
    private $context = 'front';
    private $mode = 'config';

    /**
     * Constructor
     *
     * @param array $block
     */
    public function __construct($block)
    {
        $this->block = $block;

        $this->setIdLang((int) $block['id_lang']);
        $this->setIdShop((int) $block['id_shop']);
    }

    /**
     * set the context of prettyblocks results
     * back or front only accepted
     *
     * @param string $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * force default value
     *
     * @param bool $value default true
     *
     * @return $this
     */
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

    /**
     * Allow html in the field
     *
     * @param bool $value
     *
     * @return $this
     */
    public function allowHtml($value)
    {
        $this->allow_html = (bool) $value;

        return $this;
    }

    /**
     * set the type of the field
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * get the value
     *
     * @return Any
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * get the formatted Value
     *
     * @return Any
     */
    public function getFormattedValue()
    {
        return $this->formattedValue;
    }

    /**
     * set model
     *
     * @param \PrettyBlocksModel $model
     *
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set all essential Data
     *
     * @return $this
     */
    public function get()
    {
        // set value if exists
        if (is_null($this->model) && isset($this->block['id_prettyblocks'])) {
            $this->model = new \PrettyBlocksModel((int) $this->block['id_prettyblocks'], $this->id_lang, $this->id_shop);
            $this->config = json_decode($this->model->config, true);
            $this->id_lang = (int) $this->model->id_lang;
        }
        $this->_setField();

        // set label
        if (isset($this->field['label'])) {
            $this->label = $this->field['label'];
        }
        // set type
        if (isset($this->field['type'])) {
            $this->type = $this->field['type'];
        }
        // force default value
        if (isset($this->field['force_default_value']) && $this->field['force_default_value'] === true) {
            $this->force_default_value = true;
        }

        $this->setValues();

        return $this;
    }

    /**
     * get field Data
     *
     * @param string $data
     * @param any $shouldReturn
     *
     * @return array|any
     */
    public function getFieldData($data, $shouldReturn = false)
    {
        return $this->field[$data] ?? $shouldReturn;
    }

    /**
     * Set values
     *
     * @return $this
     */
    public function setValues()
    {
        $values = $this->getFormattedConfig();
        if (isset($values[$this->key]) && is_null($this->newValue)) {
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
     *
     * @return $this
     */
    public function setNewValue($value)
    {
        $this->newValue = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        $this->get();
        $this->_setField();

        return $this;
    }

    /**
     * set Field
     *
     * @return $this
     */
    private function _setField()
    {
        if (isset($this->block['config']['fields'][$this->key])) {
            $this->field = $this->block['config']['fields'][$this->key];
        }

        return $this;
    }

    /**
     * set formatted value
     *
     * @return array
     */
    private function _setFormattedValue()
    {
        $data = $this->getFormattedConfig();
        $data[$this->key] = $this->format();

        return $data;
    }

    /**
     * set  IDland
     *
     * @param int $id_lang
     *
     * @return $this
     */
    public function setIdLang($id_lang)
    {
        $this->id_lang = (int) $id_lang;

        return $this;
    }

    /**
     * set Id Shop
     *
     * @param int $id_shop
     *
     * @return $this
     */
    public function setIdShop($id_shop)
    {
        $this->id_shop = (int) $id_shop;

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
       @return Array
    */
    public function getFormattedConfig()
    {
        $value = [];
        $jsonConfig = $this->model->config;
        if (!is_null($jsonConfig) && !\Validate::isJson($jsonConfig)) {
            return $value;
        }
        $json = json_decode($jsonConfig, true);

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
        $method = 'formatField' . ucwords(str_replace('_', '', $this->type));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return false;
    }

    public function formatForFront()
    {
        $method = 'formatField' . ucwords(str_replace('_', '', $this->type)) . 'ForFront';
        if (method_exists($this, $method)) {
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
        if ($this->model->save()) {
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

    private function formatFieldTitle()
    {
        // if value exists in DB and newValue is empty
        if(!is_null($this->value) && is_null($this->newValue))
        {
            return $this->secureTitleEntry($this->value);
        }       
        // if value doesn't exists in DB and new value is set
        if($this->force_default_value && is_null($this->newValue))
        {
            return $this->secureTitleEntry($this->field['default']);
        }

        return $this->secureTitleEntry($this->newValue);
        
    }

    /** 
     * Secure format for title component
     * @param array $array
     * @return array
     */
    private function secureTitleEntry($array)
    {

        $element = [
            'tag' => ($array['tag']) ? pSQL($array['tag']) : 'h2',
            'classes' => ($array['classes']) ? array_map('pSQL',($array['classes'])) : [],
            'value' => ($array['value']) ? $this->_clearValue($array['value']) : '',
            'focus' => (bool) $array['focus'],
            'inside' => (bool) $array['inside'],
            'bold' => (bool) $array['bold'],
            'italic' => (bool) $array['italic'],
            'underline' => (bool) $array['underline'],
            'size' => (int) $array['size'],
        ];
       return $element;
    }
    private function _clearValue($value)
    {
        $new_value = str_replace(array("\r", "\n"), '',$value);
        $new_value = str_replace("\\n", '',$new_value);

        $new_value = pSQL(\Tools::purifyHTML($new_value));
        return $new_value;
    }

    /**
     * format field text
     *
     * @return string
     */
    private function formatFieldText()
    {
        // if value exists in DB and newValue is empty
        if (!is_null($this->value) && is_null($this->newValue)) {
            return $this->secureTextEntry($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->newValue)) {
            return $this->secureTextEntry($this->field['default']);
        }

        return $this->secureTextEntry($this->newValue);
    }

    /**
     * format field color
     *
     * @return string
     */
    private function formatFieldColor()
    {
        return $this->formatFieldText();
    }

    /**
     * format field textarea
     *
     * @return string
     */
    private function formatFieldTextarea()
    {
        return $this->formatFieldText();
    }

    /**
     * format field fileupload (backend)
     *
     * @return array
     */
    private function formatFieldFileupload()
    {
        $value = [];

        if (isset($this->newValue)) {
            // if new value exists
            $value = $this->secureFileUploadEntry($this->newValue);
        } elseif (isset($this->value) && is_array($this->value)) {
            // if value exists in DB
            $value = $this->secureFileUploadEntry($this->value);
        } elseif ($this->force_default_value && !empty($this->field['default'])) {
            // if we need to use default value
            $value = $this->secureFileUploadEntry($this->field['default']);
        }

        $extension = '';
        $mediatype = '';
        $filename = '';

        if (!empty($value['url'])) {
            // add extension
            $extension = pathinfo($value['url'], PATHINFO_EXTENSION);
            // add media type (image, document, video, ...)
            $mediatype = \HelperBuilder::getMediaTypeForExtension($extension);
            // add filename
            $filename = pathinfo($value['url'], PATHINFO_BASENAME);
        }

        $value['extension'] = $extension;
        $value['mediatype'] = $mediatype;
        $value['filename'] = $filename;

        return $value;
    }

    /**
     * format field fileupload (frontend)
     *
     * @return array
     */
    private function formatFieldFileuploadForFront()
    {
        $value = $this->formatFieldFileupload();

        $size = 0;
        $width = 0;
        $height = 0;

        if (!empty($value['url'])) {
            $path = \HelperBuilder::pathFormattedFromUrl($value['url']);

            if (file_exists($path)) {
                $size = filesize($path);

                // if file is an image, we return width and height
                if ($value['mediatype'] == 'image') {
                    list($width, $height) = getimagesize($path);
                }
            }
        }

        $value['size'] = (int) $size;
        $value['width'] = (int) $width;
        $value['height'] = (int) $height;

        return $value;
    }

    /**
     * format field editor in HTML
     *
     * @return string
     */
    private function formatFieldEditor()
    {
        $this->allow_html = true;

        return $this->formatFieldText();
    }

    /**
     * return the value for PrettyBlocks (backend)
     *
     * @return bool
     */
    private function formatFieldCheckbox()
    {
        // if value exists in DB and newValue is empty

        if (!is_null($this->value) && is_null($this->newValue)) {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN) ?? false;
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->newValue)) {
            return filter_var($this->field['default'], FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        return filter_var($this->newValue, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * return the value for PrettyBlocks (backend)
     *
     * @return string
     */
    private function formatFieldRadioGroup()
    {
        if (!is_array($this->field['choices'])) {
            return '';
        }
        // if value exists in DB and newValue is empty
        if (!is_null($this->value) && empty($this->newValue) && isset($this->field['choices'][$this->value])) {
            return pSQL($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->newValue)) {
            if (is_array($this->field['choices'])
            && isset($this->field['default'], $this->field['choices'][$this->field['default']])
            ) {
                return pSQL($this->field['default']);
            }

            // get default value
            if (is_array($this->field['choices'] && !empty($this->field['choices']))) {
                reset($this->field['choices']);
                $firstKey = key($this->field['choices']);

                return pSQL($firstKey);
            }
        }
        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->field['choices']) && isset($this->field['choices'][$this->newValue])) {
            return pSQL($this->newValue);
        }
        // if no matches.
        return '';
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return string
     */
    private function formatFieldRadioGroupForFront()
    {
        if (!is_array($this->field['choices'])) {
            return '';
        }
        // if value exists in DB and newValue is empty
        if (!is_null($this->value) && empty($this->newValue) && (!is_array($this->value) && isset($this->field['choices'][$this->value]))) {
            if ($this->allow_html) {
                return pSQL(\Tools::purifyHTML($this->field['choices'][$this->value]));
            }

            return pSQL($this->field['choices'][$this->value]);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && $this->newValue == '') {
            if (is_array($this->field['choices'])
            && isset($this->field['default'], $this->field['choices'][$this->field['default']])
            ) {
                return $this->field['choices'][$this->field['default']];
            }

            // get default value
            if (is_array($this->field['choices'] && !empty($this->field['choices']))) {
                reset($this->field['choices']);
                $firstKey = key($this->field['choices']);

                return pSQL($this->field['choices'][$firstKey]);
            }
        }
        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->field['choices']) && !is_array($this->newValue) && isset($this->field['choices'][$this->newValue])) {
            if ($this->allow_html) {
                return pSQL(\Tools::purifyHTML($this->field['choices'][$this->newValue]));
            }

            return pSQL($this->field['choices'][$this->newValue]);
        }
        // if no matches.
        return '';
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return string
     */
    private function formatFieldSelectForFront()
    {
        return $this->formatFieldRadioGroupForFront();
    }

    /**
     * format the value for select field and radioGroup for PrettyBlocks (backend)
     */
    private function formatFieldSelect()
    {
        return $this->formatFieldRadioGroup();
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return string
     */
    private function formatFieldMultiselectForFront()
    {
        return $this->formatFieldMultiselect();
    }

    /**
     * format the value for select field and radioGroup for PrettyBlocks (backend)
     */
    private function formatFieldMultiselect()
    {
        // print_r($this->field);die();
        if (empty($this->field['choices'])) {
            return [];
        }
        // if value exists in DB and newValue is empty
        if (!is_null($this->value) && !isset($this->newValue)) {
            return array_filter($this->value, function ($val) {
                return in_array($val, array_keys($this->field['choices']));
            });
        }
        // if value doesn't exists in DB and new value is not set return default value
        if ($this->force_default_value && !isset($this->newValue) && isset($this->field['default'])) {
            return $this->field['default'];
        }
        // if new value is set and force default value is false retrun new value
        if (isset($this->newValue) && is_array($this->newValue)) {
            return array_filter($this->newValue, function ($val) {
                return in_array($val, array_keys($this->field['choices']));
            });
        }
        // if no matches.
        return [];
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return array|bool
     */
    private function formatFieldSelectorForFront()
    {
        // if value exists in DB && newValue is empty
        if (!is_null($this->value) && empty($this->newValue) && is_array($this->value) && isset($this->value['show']['id'])) {
            $idCollection = (int) $this->value['show']['id'];

            return $this->_getCollection($idCollection, $this->field['collection']);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && $this->newValue == '') {
            $idCollection = (int) $this->field['default']['show']['id'];

            return $this->_getCollection($idCollection, $this->field['collection']);
        }

        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->newValue) && isset($this->newValue['show']['id'])) {
            $idCollection = (int) $this->newValue['show']['id'];

            return $this->_getCollection($idCollection, $this->field['collection']);
        }
        // if no matches.
        return false;
    }

    /**
     * formatFieldSelector
     *
     * @return string|bool
     */
    private function formatFieldSelector()
    {
        // if value exists in DB && newValue is empty
        if (!is_null($this->value) && empty($this->newValue) && is_array($this->value) && isset($this->value['show']['id'])) {
            return $this->secureCollectionEntry($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && $this->newValue == '') {
            return $this->secureCollectionEntry($this->field['default']);
        }

        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->newValue) && isset($this->newValue['show']['id'])) {
            return $this->secureCollectionEntry($this->newValue);
        }
        // if no matches.
        return false;
    }

    /**
     * get Collection by Id and Name
     *
     * @param int $id
     * @param string $collectionName
     * @param string $primaryField
     *
     * @return ObjectPresenter|bool
     */
    private function _getCollection($id, $collectionName, $primaryField = null)
    {
        $c = new \PrestaShopCollection($collectionName, $this->id_lang);
        $primary = $primaryField ?? 'id_' . \Tools::strtolower($collectionName);

        $object = $c->where($primary, '=', (int) $id)->getFirst();
        if (!\Validate::isLoadedObject($object)) {
            return false;
        }
        $objectPresenter = new ObjectPresenter();

        return $objectPresenter->present($object);
    }

    /**
     * Secure format for selector
     *
     * @param array $array
     *
     * @return array
     */
    private function secureCollectionEntry($array)
    {
        $secure = [];
        $secure['show'] = [
            'id' => (int) $array['show']['id'],
            'primary' => (int) $array['show']['primary'],
            'name' => pSQL($array['show']['name']),
            'formatted' => pSQL($array['show']['formatted']),
        ];

        return $secure;
    }

    /**
     * Secure format for text
     *
     * @param string $string
     *
     * @return string
     */
    private function secureTextEntry($string)
    {
        if ($this->allow_html) {
            return \Tools::purifyHTML($string);
        }

        return \Tools::purifyHTML(strip_tags($string));
    }

    /**
     * Secure format for fileUpload
     *
     * @param array $array
     *
     * @return array
     */
    private function secureFileUploadEntry($array)
    {
        $secure = [];
        $url = '';
        if (!isset($array['url'])) {
            if ($this->force_default_value && isset($this->field['default']['url'])) {
                $url = $this->field['default']['url'];
            }
        } elseif (isset($array['url']) && $array['url'] !== '') {
            $url = $array['url'];
        } elseif ($this->force_default_value && isset($this->field['default']['url'])) {
            $url = $this->field['default']['url'];
        }
        $secure['url'] = pSQL($url);

        return $secure;
    }
}
