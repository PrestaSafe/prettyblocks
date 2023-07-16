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

namespace PrestaSafe\PrettyBlocks\Fields;

use PrestaSafe\PrettyBlocks\Core\FieldCore;

// $field = (new FieldUpload)
// ->setBlock($block)
// ->setContext('_settings')
// ->setSettings($settings)
// ->format()
// ->save()
// ->renderFront()
class FileUpload extends FieldCore
{
    public $key;
    public $type = 'fileupload';
    public $block = false;
    public $context = '_settings';
    public $name = '';
    public $default_value = '';
    public $value = '';

    public function __construct()
    {
    }

    public function setBlock($block)
    {
        $this->block = $block;

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function format()
    {
        // return format
        $key = $this->getKey();

        $default_value = $this->default_value ?? '';
        $res = \Configuration::get($key);
        if (!$res) {
            $res = $default_value;
        } else {
            $res = json_decode($res, true);
        }
        $this->value = $res;

        return $this;
    }

    private function getKey()
    {
        if ($this->key == null) {
            if ($this->block) {
                $this->key = \Tools::strtoupper($this->name . $this->context);
            } else {
                $this->key = \Tools::strtoupper($this->block['id_prettyblocks'] . '_' . $this->name . $this->context);
            }
        }

        return $this;
    }

    public function setSettings($settings)
    {
        if (!is_array($settings)) {
            $settings = [$settings];
        }
        foreach ($settings as $key => $value) {
            if (isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    public function save()
    {
        if ($this->context !== '_state') {
            if ($this->value != $this->default_value) {
                $this->getKey();
                \Configuration::updateValue($this->key, $this->value, true);
            }
        } else {
            // save block
        }
    }

    public function value()
    {
    }
}
