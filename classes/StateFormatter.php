<?php 
/**
 * Please don't judge me
 * it will be refactoring soon. 
 */
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;
class StateFormatter
{
    public static function formatFieldUpload($value)
    {
        return ($value['value']['url']) ? $value['value'] : ['url' => ''];
    }

    public static function formatFieldSelector($value)
    {
        if($value['collection'])
        {
            $json = ($value['value']);
            if(!isset($json['show']['id']))
            {
                return false;
            }
            // @TODO -> Presenter
            $c = new PrestaShopCollection($value['collection'], Context::getContext()->language->id);
            $primary = ($value['primary']) ?? 'id_'.Tools::strtolower($value['collection']);
            $object = $c->where($primary, '=' , (int)$json['show']['id'])->getFirst();
            $objectPresenter = new ObjectPresenter();
            // dump($object);
            return $objectPresenter->present($object);
        }
           
        
        return false;
    }


    public static function formatFieldDefault($value)
    {
        return ($value['value']) ?? '';
    }

}
