<?php

use PrestaSafe\PrettyBlocks\Core\PrettyBlocksField;



class PrettyBlocksMigrate
{
    /** 
     * add field template to database
     * @return bool
     */
    static function addTemplateField()
    {
        $sql = [];
        $res = true;
        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'prettyblocks` ADD `template` longtext DEFAULT NULL AFTER `config`;';
        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'prettyblocks` ADD `default_params` longtext DEFAULT NULL AFTER `config`;';
        foreach($sql as $query)
        {
            $res &= \Db::getInstance()->execute($query);   
        }
        return $res;
    }


    static function migrateConfig()
    {
        self::addTemplateField();
        $langs = \Language::getLanguages();
        $res = true;
        foreach($langs as $lang) {
            $lang_id = $lang['id_lang'];
            $blocks = (new \PrestaShopCollection('PrettyBlocksModel', $lang_id))->getAll();
            // get old config
            foreach($blocks as $model) {
                $block = $model->mergeStateWithFields();
                $config = $block['config']['fields'] ?? [];
                if (is_array($config) && count($config) > 0) {
                    foreach ($config as $field => $value) {
                        $formatted[$field] = self::_formatFieldConfigFront($field, $value, $block, 'back');
                    }
                }
                // convert in fields and save
               

                foreach($formatted as $name => $data)
                {
                    $field = (new PrettyBlocksField($block))
                    ->setKey($name)
                    ->setNewValue($data)
                    ->save();
                 
                }
                // moving template to model
                $model->setCurrentTemplate( pSQL(self::_getTemplateSelected($block)) );
                $model->setDefaultParams(self::_getDefaultParams($block));
                // moving default params to model

                $model->save();
                // destroy configuration
                $res &= $model->removeConfig();
            }
        }
        return $res;
    }
    /** 
     * Get default params in Configuration
     * @param array $block
     * @return array
     */
    private static function _getDefaultParams($block)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = \Tools::strtoupper($id_prettyblocks . '_default_params');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $options = [
            'container' => true,
            'load_ajax' => false,
            'bg_color' => '',
        ];
        $defaultParams = \Configuration::get($key);
        if (!$defaultParams) {
            return $options;
        }

        return json_decode($defaultParams, true);
    }

    /** 
     * get template in Configuration 
     * @param array $block
     * @return string
     */
    private static function _getTemplateSelected($block){
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = \Tools::strtoupper($id_prettyblocks . '_template');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $default_tpl = (isset($block['templates']['default'])) ? 'default' : 'welcome';
        $currentTemplate = \Configuration::get($key, null, null, (int) $block['id_shop']);
        if ($currentTemplate !== false && isset($block['templates'][$currentTemplate])) {
            return $currentTemplate;
        }
        return $default_tpl;

    }

    private static function _formatFieldConfigFront($field, $value, $block, $context = 'front')
    {
        \FieldFormatter::setSuffix('_config');

        switch ($value['type']) {
            case 'editor':
                return \FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'text':
                return \FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'textarea':
                return \FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'color':
                return \FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'radio':
                return \FieldFormatter::formatFieldBoxes($field, $value, $block, $context);
                break;
            case 'checkbox':
                return \FieldFormatter::formatFieldBoxes($field, $value, $block, $context);
                break;
            case 'fileupload':
                return \FieldFormatter::formatFieldUpload($field, $value, $block, $context);
                break;
            case 'upload':
                return \FieldFormatter::formatFieldUpload($field, $value, $block, $context);
                break;
            case 'selector':
                return \FieldFormatter::formatFieldSelector($field, $value, $block, $context);
                break;
            case 'select':
                return \FieldFormatter::formatFieldSelect($field, $value, $block, $context);
                break;
            case 'radio_group':
                return \FieldFormatter::formatFieldRadioGroup($field, $value, $block, $context);
                break;
            default:
                return '';
        }
    }
}