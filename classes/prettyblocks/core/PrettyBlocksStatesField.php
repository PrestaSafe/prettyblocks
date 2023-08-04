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

class PrettyBlocksStatesField extends PrettyBlocksField
{
    public $states = [];
    public $id_lang = 0;
    public $id_shop = 0;
    public $model;
    public $index = 0;
    public $key = '';
    public $formattedValue = '';
    public $field = [];
    public $force_default_value = false;
    public $label = '';
    public $type = '';

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
            $this->states = json_decode($this->model->state, true);
            $this->id_lang = (int) $this->model->id_lang;
        }
        $this->_setFieldState();

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

    private function _setFieldState()
    {
        if (isset($this->block['repeater_db'][$this->index][$this->key])) {
            $this->field = $this->block['repeater_db'][$this->index][$this->key];
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        $this->get();
        $this->_setFieldState();

        return $this;
    }

    public function setValues()
    {
        //  bug
        $values = $this->getFormattedConfig();
        // dump($this->field);
        if (isset($values[$this->index][$this->key]) && is_null($this->newValue)) {
            $this->value = $values[$this->index][$this->key];
        } else {
            $this->formattedValue = $this->formatForFront();
            $this->field['value'] = $this->formattedValue;
            $this->value = $this->field;
        }

        return $this;
    }

    public function getFormattedConfig()
    {
        $value = [];

        $jsonConfig = $this->model->state;
        if (!is_null($jsonConfig) && !\Validate::isJson($jsonConfig)) {
            return $value;
        }
        $json = json_decode($jsonConfig, true);

        return $json;
    }

    public function save()
    {
        $json = $this->_setFormattedValue();
        $json = json_encode($json, true);
        $this->model->state = $json;
        if ($this->model->save()) {
            $this->_assignValues($this->newValue);
        }

        return $this;
    }

    /**
     * set formatted value
     *
     * @return array
     */
    public function _setFormattedValue()
    {
        $data = $this->getFormattedConfig();
        $data[$this->index][$this->key] = $this->format();

        $this->formattedValue = $this->formatForFront();
        $this->field['value'] = $this->formattedValue;

        return $data;
    }
}
