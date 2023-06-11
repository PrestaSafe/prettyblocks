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
use PrestaSafe\PrettyBlocks\Core\Interface\ComponentInterface;

class Title implements ComponentInterface{

    private $tag;
    private $value;
    private $classes;

    public function __construct($tag, $value, $classes = [])
    {
        $this->tag = $tag;
        $this->value = $value;
        $this->classes = $classes;
    }

    public function render()
    {
        $context = \Context::getContext();

        $smarty = $context->smarty;
        $smarty->assign('tag', $this->tag);
        $smarty->assign('value', $this->value);
        $smarty->assign('classes', $this->getClasses());

        return $context->smarty->fetch('module:prettyblocks/views/templates/front/components/title.tpl');

    }

    private function getClasses()
    {
        return implode(' ', $this->classes);
    }
   
}