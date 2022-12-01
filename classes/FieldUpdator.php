<?php 
/**
 * Please don't judge me
 * it will be refactoring soon. 
 */

class FieldUpdator
{
    public static function updateFieldText($name,$value = false,$block = false,$suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        if($value !== false)
        {
            Configuration::updateValue($key, $value);
        }
    }

    public static function updateFieldBoxes($name,$value = false,$block = false,$suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);

        if($value !== false)
        {
            $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            if(!$bool)
            {
                $bool = 0;
            }else{
                $bool = 1;
            }
           return Configuration::updateValue($key, $bool);
        }
        Configuration::updateValue($key, 0);
    }
    public static function updateFieldUpload($name,$value = false,$block = false,$suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        if($value !== false)
        {
            $format = $value;
            $to_json = json_encode($format);
            Configuration::updateValue($key, $to_json);
        }
    }
    public static function updateFieldSelector($name,$value = false,$block = false,$suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        if(!isset($value['show']))
        {
            return false;
        }
        $collection = json_encode($value, true);
        Configuration::updateValue($key, $collection);
    }

    public static function updateFieldSelect($name,$value = false,$block = false,$suffix = '_config')
    {
       self::updateFieldText($name,$value,$block,$suffix);
    }
    
    public static function updateFieldRadioGroup($name,$value = false,$block = false,$suffix = '_config')
    {
        self::updateFieldText($name,$value,$block,$suffix);
    }
    

    private static function getKey($name, $block = false, $suffix = '_config')
    {
        if(!$block)
        {
            $key = Tools::strtoupper($name.$suffix);
        }else {
            $key = Tools::strtoupper($block['id_prettyblocks'].'_'.$name.$suffix);
        }
        return $key;
    }

    public static function updateFieldEditor($name,$value = false,$block = false,$suffix = '_config')
    {
        $key = self::getKey($name, $block, $suffix);
        if($value !== false)
        {
            Configuration::updateValue($key, $value, true);
        }
    }
    
}