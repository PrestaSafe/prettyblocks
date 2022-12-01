<?php 
class TplSettings {
    static function getSettings($key, $default = '')
    {        
        $smarty_vars = self::getVars();
        $result = (isset($smarty_vars['prettyblocks']['theme_settings'][$key])) 
            ? $smarty_vars['prettyblocks']['theme_settings'][$key] 
            : $default;
        
        return $result;
    }

    public static function getVars()
    {
        $cache_id = 'CZBuilder::getSmartyVars';
        if (!Cache::isStored($cache_id)) {
            // $smarty_vars = Context::getContext()->smarty->getTemplateVars();
            $smarty_vars = Hook::exec('ActionFrontControllerSetVariables',[], null, true);
            // dump($smarty_vars);
            Cache::store($cache_id, $smarty_vars);
        }
        return Cache::retrieve($cache_id);
    }

   
}