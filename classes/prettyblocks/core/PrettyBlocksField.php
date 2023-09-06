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

class PrettyBlocksField
{
    public $block;
    public $type = 'text';
    public $config = [];
    public $states = [];
    public $settings = [];
    public $id_lang = 0;
    public $id_shop = 0;

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
        $this->setFields($block);
    }

    /**
     * Set id_lang
     *
     * @param int $id_lang
     *
     * @return self
     */
    public function setIdLang($id_lang)
    {
        $this->id_lang = $id_lang;

        return $this;
    }

    /**
     * Set id_shop
     *
     * @param int $id_shop
     *
     * @return self
     */
    public function setIdShop($id_shop)
    {
        $this->id_shop = $id_shop;

        return $this;
    }

    public function setFields($fields)
    {
        $this->setData();
        $this->setConfigFields();
        $this->setStatesFields();

        return $this;
    }

    /**
     * setConfigFields
     *
     * @return $this
     */
    public function setConfigFields()
    {
        if (!isset($this->block['config']['fields'])) {
            return [];
        }

        $fields = [];
        $configDaved = $this->block['config_json'];

        foreach ($this->block['config']['fields'] as $key => $field) {
            $field['id_lang'] = $this->id_lang;
            $field['id_shop'] = $this->id_shop;
            if (isset($configDaved[$key]['value'])) {
                // merged value db if exist
                $field['value'] = $configDaved[$key]['value'];
            }
            $fields[$key] = (new FieldCore($field));
        }
        $this->config = $fields;

        return $this;
    }

    public function getConfigFields()
    {
        return $this->config;
    }

    public function setStatesFields()
    {
        if (!isset($this->block['states_json'])) {
            return [];
        }

        $fields = [];
        foreach ($this->block['states_json'] as $index => $data) {
            $stateFields = [];
            foreach ($data as $key => $value) {
                $value['id_lang'] = $this->id_lang;
                $value['id_shop'] = $this->id_shop;
                $stateFields[$key] = (new FieldCore($value));
            }
            $fields[$index] = $stateFields;
        }
        $this->states = $fields;

        return $this;
    }

    public function getStatesFields()
    {
        return $this->states;
    }

    public function setData()
    {
        $this->id_lang = (int) $this->block['id_lang'];
        $this->id_shop = (int) $this->block['id_shop'];
    }

    public function setKey($key)
    {
        return $this;
    }

    public function setContext()
    {
        return $this;
    }
}
