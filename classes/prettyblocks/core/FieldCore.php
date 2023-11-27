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

class FieldCore
{
    public $type;
    public $label;
    public $path;
    public $collection;
    public $selector;
    public $default;
    public $choices;
    public $force_default_value = true;
    public $value;
    public $new_value;
    public $allow_html = false;
    public $id_lang = 0;
    public $id_shop = 0;

    /**
     * __construct
     *
     * @param mixed $data
     *
     * @return void
     */
    public function __construct($data = [])
    {
        $this->setAttributeS($data);

        if ($this->id_lang == 0) {
            $this->id_lang = \Context::getContext()->language->id;
        }

        if ($this->id_shop == 0) {
            $this->id_shop = \Context::getContext()->shop->id;
        }
    }

    /**
     * setAttributeS
     *
     * @param mixed $data
     *
     * @return void
     */
    public function setAttributeS($data)
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * setAttribute
     *
     * @param mixed $attribute
     * @param mixed $value
     *
     * @return void
     */
    public function setAttribute($attribute, $value)
    {
        if (property_exists($this, $attribute)) {
            $this->{$attribute} = $value;
        }

        return $this;
    }

    /**
     * getAttribute
     *
     * @param mixed $attribute
     *
     * @return any
     */
    public function getAttribute($attribute)
    {
        if (property_exists($this, $attribute)) {
            return $this->{$attribute};
        }

        return null;
    }

    /**
     * compile
     *
     * @return array
     */
    public function compile()
    {
        $data = [];
        if ($this->type) {
            $data['type'] = $this->type;
        }
        if ($this->label) {
            $data['label'] = $this->label;
        }
        if ($this->path) {
            $data['path'] = $this->path;
        }
        if ($this->collection) {
            $data['collection'] = $this->collection;
        }
        if ($this->selector) {
            $data['selector'] = $this->selector;
        }
        if ($this->default) {
            $data['default'] = $this->default;
        }
        if ($this->choices) {
            $data['choices'] = $this->choices;
        }
        if ($this->force_default_value) {
            $data['force_default_value'] = $this->force_default_value;
        }

        $data['value'] = $this->format();

        return $data;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return $this->compile();
    }

    /**
     * getFrontValue
     *
     * @return any
     */
    public function getFrontValue()
    {
        return $this->formatForFront();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getFormattedValue()
    {
        return $this->getValue();
    }

    /*
        |
        |--------------------------------------------------------------------------
        | Format the value for PrettyBlocks backend
        |--------------------------------------------------------------------------
        |
    */

    /**
     * format
     *
     * @return any
     */
    public function format()
    {
        $method = 'formatField' . ucwords(str_replace('_', '', $this->type));

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return false;
    }

    /**
     * formatForFront
     *
     * @return any
     */
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
        | Formatted Method
        |--------------------------------------------------------------------------
        |
    */

    /**
     * formatFieldTitle
     *
     * @return void
     */
    public function formatFieldTitle()
    {
        // if value exists in DB and new_value is empty
        if (!is_null($this->value) && is_null($this->new_value)) {
            return $this->secureTitleEntry($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->new_value)) {
            return $this->secureTitleEntry($this->default);
        }

        return $this->secureTitleEntry($this->new_value);
    }

    /**
     * Secure format for title component
     *
     * @param array $array
     *
     * @return array
     */
    public function secureTitleEntry($array)
    {
        if (!is_array($array)) {
            $secure = [];
            $secure['tag'] = 'p';
            $secure['classes'] = [];
            $secure['value'] = $array;
            $secure['focus'] = false;
            $secure['inside'] = false;
            $secure['bold'] = false;
            $secure['italic'] = false;
            $secure['underline'] = false;
            $secure['size'] = 0;
            $array = $secure;
        }
        $element = [
            'tag' => ($array['tag']) ? pSQL($array['tag']) : 'h2',
            'classes' => (isset($array['classes'])) ? array_map('pSQL', $array['classes']) : [],
            'value' => ($array['value']) ? $this->_clearValue($array['value']) : '',
            'focus' => (bool) (isset($array['focus'])) ? $array['focus'] : false,
            'inside' => (bool) (isset($array['inside'])) ? $array['inside'] : true,
            'bold' => (bool) (isset($array['bold'])) ? $array['bold'] : false,
            'italic' => (bool) (isset($array['italic'])) ? $array['italic'] : false,
            'underline' => (bool) (isset($array['underline'])) ? $array['underline'] : false,
            'size' => (int) (isset($array['size'])) ? $array['size'] : 12,
        ];

        return $element;
    }

    /**
     * _clearValue
     *
     * @param mixed $value
     *
     * @return void
     */
    public function _clearValue($value)
    {
        $new_value = str_replace(["\r", "\n"], '', $value);
        $new_value = str_replace('\\n', '', $new_value);

        $new_value = \Tools::purifyHTML($new_value);

        return $new_value;
    }

    /**
     * format field text
     *
     * @return string
     */
    public function formatFieldText()
    {
        // if value exists in DB and new_value is empty
        if (!is_null($this->value) && is_null($this->new_value)) {
            return $this->secureTextEntry($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->new_value)) {
            return $this->secureTextEntry($this->default);
        }

        return $this->secureTextEntry($this->new_value);
    }

    /**
     * format field color
     *
     * @return string
     */
    public function formatFieldColor()
    {
        return $this->formatFieldText();
    }

    /**
     * format field textarea
     *
     * @return string
     */
    public function formatFieldTextarea()
    {
        return $this->formatFieldText();
    }

    /**
     * format field fileupload (backend)
     *
     * @return array
     */
    public function formatFieldFileupload()
    {
        $value = [];

        if (isset($this->new_value)) {
            // if new value exists
            $value = $this->secureFileUploadEntry($this->new_value);
        } elseif (isset($this->value) && is_array($this->value)) {
            // if value exists in DB
            $value = $this->secureFileUploadEntry($this->value);
        } elseif ($this->force_default_value && !empty($this->default)) {
            // if we need to use default value
            $value = $this->secureFileUploadEntry($this->default);
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
    public function formatFieldFileuploadForFront()
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
    public function formatFieldEditor()
    {
        $this->allow_html = true;

        return $this->formatFieldText();
    }

    /**
     * return the value for PrettyBlocks (backend)
     *
     * @return bool
     */
    public function formatFieldCheckbox()
    {
        // if value exists in DB and new_value is empty

        if (!is_null($this->value) && is_null($this->new_value)) {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN) ?? false;
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->new_value)) {
            return filter_var($this->default, FILTER_VALIDATE_BOOLEAN) ?? false;
        }

        return filter_var($this->new_value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * return the value for PrettyBlocks (backend)
     *
     * @return string
     */
    public function formatFieldRadioGroup()
    {
        if (!is_array($this->choices)) {
            return '';
        }
        // if value exists in DB and new_value is empty
        if (!is_null($this->value) && empty($this->new_value) && isset($this->choices[$this->value])) {
            return $this->value;
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && is_null($this->new_value)) {
            if (is_array($this->choices)
            && isset($this->default, $this->choices[$this->default])
            ) {
                return $this->default;
            }

            // get default value
            if (is_array($this->choices && !empty($this->choices))) {
                reset($this->choices);
                $firstKey = key($this->choices);

                return $firstKey;
            }
        }
        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->choices) && isset($this->choices[$this->new_value])) {
            return $this->new_value;
        }

        // if no matches.
        return '';
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return string
     */
    public function formatFieldRadioGroupForFront()
    {
        if (!is_array($this->choices)) {
            return '';
        }
        // if value exists in DB and new_value is empty
        if (!is_null($this->value) && empty($this->new_value) && (!is_array($this->value) && isset($this->choices[$this->value]))) {
            if ($this->allow_html) {
                return \Tools::purifyHTML($this->choices[$this->value]);
            }

            return $this->choices[$this->value];
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && $this->new_value == '') {
            if (is_array($this->choices)
            && isset($this->default, $this->choices[$this->default])
            ) {
                return $this->choices[$this->default];
            }

            // get default value
            if (is_array($this->choices && !empty($this->choices))) {
                reset($this->choices);
                $firstKey = key($this->choices);

                return $this->choices[$firstKey];
            }
        }
        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->choices) && !is_array($this->new_value) && isset($this->choices[$this->new_value])) {
            if ($this->allow_html) {
                return \Tools::purifyHTML($this->choices[$this->new_value]);
            }

            return $this->choices[$this->new_value];
        }

        // if no matches.
        return '';
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return string
     */
    public function formatFieldSelectForFront()
    {
        return $this->formatFieldRadioGroupForFront();
    }

    /**
     * format the value for select field and radioGroup for PrettyBlocks (backend)
     */
    public function formatFieldSelect()
    {
        return $this->formatFieldRadioGroup();
    }

    /**
     * return the value for PrettyBlocks (frontend)
     *
     * @return string
     */
    public function formatFieldMultiselectForFront()
    {
        // update of perfs by Jeff
        if (is_array($this->value)) {
            return $this->value;
        }
        // if value doesn't exists in DB and new value is not set return default value
        if ($this->force_default_value && isset($this->default)) {
            return $this->default;
        }
    }

    /**
     * format the value for select field and radioGroup for PrettyBlocks (backend)
     */
    public function formatFieldMultiselect()
    {
        // print_r($this->field);die();
        if (empty($this->choices)) {
            return [];
        }
        // if value exists in DB and new_value is empty
        if (!is_null($this->value) && !isset($this->new_value)) {
            return array_filter($this->value, function ($val) {
                return in_array($val, array_keys($this->choices));
            });
        }
        // if value doesn't exists in DB and new value is not set return default value
        if ($this->force_default_value && !isset($this->new_value) && isset($this->default)) {
            return $this->default;
        }
        // if new value is set and force default value is false retrun new value
        if (isset($this->new_value) && is_array($this->new_value)) {
            return array_filter($this->new_value, function ($val) {
                return in_array($val, array_keys($this->choices));
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
    public function formatFieldSelectorForFront()
    {
        // if value exists in DB && new_value is empty
        if (!is_null($this->value) && empty($this->new_value) && is_array($this->value) && isset($this->value['show']['id'])) {
            $idCollection = (int) $this->value['show']['id'];

            return $this->_getCollection($idCollection, $this->collection);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && $this->new_value == '') {
            $idCollection = (int) $this->default['show']['id'];

            return $this->_getCollection($idCollection, $this->collection);
        }

        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->new_value) && isset($this->new_value['show']['id'])) {
            $idCollection = (int) $this->new_value['show']['id'];

            return $this->_getCollection($idCollection, $this->collection);
        }

        // if no matches.
        return false;
    }

    /**
     * formatFieldSelector
     *
     * @return string|bool
     */
    public function formatFieldSelector()
    {
        // if value exists in DB && new_value is empty
        if (!is_null($this->value) && empty($this->new_value) && is_array($this->value) && isset($this->value['show']['id'])) {
            return $this->secureCollectionEntry($this->value);
        }
        // if value doesn't exists in DB and new value is set
        if ($this->force_default_value && $this->new_value == '') {
            return $this->secureCollectionEntry($this->default);
        }

        // if value doesn't exists in DB and new value is set and force default value is false
        if (is_array($this->new_value) && isset($this->new_value['show']['id'])) {
            return $this->secureCollectionEntry($this->new_value);
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
    public function _getCollection($id, $collectionName, $primaryField = null)
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
    public function secureCollectionEntry($array)
    {
        $secure = [];
        $secure['show'] = [
            'id' => (int) $array['show']['id'],
            'primary' => (int) $array['show']['primary'],
            'name' => $array['show']['name'],
            'formatted' => $array['show']['formatted'],
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
    public function secureTextEntry($string)
    {
        if (is_array($string) && isset($string['value'])) {
            $string = $string['value'];
        }
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
    public function secureFileUploadEntry($array)
    {
        $secure = [];
        $url = '';
        if (!isset($array['url'])) {
            if ($this->force_default_value && isset($this->default['url'])) {
                $url = $this->default['url'];
            }
        } elseif (isset($array['url']) && $array['url'] !== '') {
            $url = $array['url'];
        } elseif ($this->force_default_value && isset($this->default['url'])) {
            $url = $this->default['url'];
        }
        $secure['url'] = $url;

        return $secure;
    }
}
