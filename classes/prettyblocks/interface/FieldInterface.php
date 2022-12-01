<?php 

namespace PrestaSafe\PrettyBlock\Interface;
interface FieldInterface
{
    public $key;
    public $type; 
    public $block = false;
    public $context = '_settings';


    public function format();

    public function setConfig();

    public function save();


    public function value();

    public function getContext();
    
}