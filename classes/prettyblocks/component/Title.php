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

namespace PrestaSafe\PrettyBlocks\Core\Components;

use PrestaSafe\PrettyBlocks\Interfaces\ComponentInterface;

class Title implements ComponentInterface
{
    private $tag;
    private $value;
    private $value_from_block = false;
    private $classes = [];
    private $block;
    private $field;
    private $attributes = [];
    private $attributesRendered = [];
    // if in state
    private $index;

    public function __construct($tag = null, $classes = [], $block, $field)
    {
        $this->tag = $tag ?? 'h1';
        $this->classes = $classes;
        $this->block = $block;
        $this->field = $field;
    }

    public function setValueFromBlock($value)
    {
        $this->value_from_block = (bool) $value;

        return $this;
    }

    public function setIndex($index)
    {
        $this->index = (int) $index;

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        if ($this->value_from_block) {
            if (!is_null($this->index)) {
                $block_value = $this->block['states'][$this->index][$this->field]['value'];
            } else {
                $block_value = $this->block['settings_formatted'][$this->field]['value'];
            }
            if (!is_array($block_value)) {
                $this->value = $block_value;
                $this->tag = 'p';
            } else {
                $this->value = $block_value['value'];
            }
            if ($this->tag !== null && is_array($block_value) && $block_value['tag']) {
                $this->tag = $block_value['tag'];
            }
        }
        $this->attributes = [
            'focus' => $block_value['focus'] ?? false,
            'bold' => $block_value['bold'] ?? false,
            'italic' => $block_value['italic'] ?? false,
            'underline' => $block_value['underline'] ?? false,
            'size' => $block_value['size'] ?? 0,
        ];

        return $this;
    }

    public function render()
    {
        $context = \Context::getContext();

        $smarty = $context->smarty;
        $smarty->assign('tag', $this->tag);
        $smarty->assign('value', $this->value);
        $smarty->assign('classes', $this->getClasses());
        $smarty->assign('block', $this->block);
        $smarty->assign('field', $this->field);
        $smarty->assign('attributes', $this->attributes);
        $smarty->assign('attributesHTML', $this->renderAttributes());
        if (!is_null($this->index)) {
            $smarty->assign('index', $this->index);
        }

        return $smarty->fetch('module:prettyblocks/views/templates/front/components/title.tpl');
    }

    public function renderAttributes()
    {
        return json_encode($this->attributes);
    }

    private function getClasses()
    {
        return implode(' ', $this->classes);
    }
}
