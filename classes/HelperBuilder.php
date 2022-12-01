<?php

class HelperBuilder
{
    public static function pathFormatterFromString($path)
    {
        if (substr($path, 0, 1) !== "$") {
            throw new Exception('Path "' . $path . '" should begin by $ ex: "$/prettyblocks/path/to/images/"');
        }
        $pathFormatted = str_replace('$', _PS_ROOT_DIR_ , $path);
        return realpath($pathFormatted) . '/';
    }

    

    /**
     * @todo Security Check
     */
    public static function pathFormatterFromUrl($path, $path_only = false)
    {
        $shop_domain = Tools::getShopDomainSsl(true);
        $pathFormatted = str_replace($shop_domain, _PS_ROOT_DIR_, $path);
        $file_name = pathinfo($pathFormatted, PATHINFO_BASENAME);
        if ($path_only) {
            return realpath(dirname($pathFormatted));
        }
        return realpath(dirname($pathFormatted)) . '/' . $file_name;
    }


    public static function hookToArray($hookName, $params = [])
    {
        $extraContent  = Hook::exec($hookName, $params, null, true);
        $res = [];
        if (is_array($extraContent)) {
            foreach ($extraContent as $formField) {
                if(!is_array($formField))
                {
                    continue;
                }
                foreach ($formField as $array) {
                    $res[] = $array;
                }
            }
        }
        return $res;
    }
}
