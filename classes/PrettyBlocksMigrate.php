<?php



class PrettyBlocksMigrate
{
    static function migrateConfig()
    {
        $langs = Language::getLanguages();
        foreach($langs as $lang) {
            $lang_id = $lang['id_lang'];
            $blocks = (new PrestaShopCollection('PrettyBlocksModel', $lang_id))->getAll();
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
                    $field = (new FieldMaker($block))
                    ->setKey($name)
                    ->setNewValue($data)
                    ->save();
                 
                }
                // destroy configuration
                $model->removeConfig(true);
            }
        }
    }

    private static function _formatFieldConfigFront($field, $value, $block, $context = 'front')
    {
        FieldFormatter::setSuffix('_config');

        switch ($value['type']) {
            case 'editor':
                return FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'text':
                return FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'textarea':
                return FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'color':
                return FieldFormatter::formatFieldText($field, $value, $block, $context);
                break;
            case 'radio':
                return FieldFormatter::formatFieldBoxes($field, $value, $block, $context);
                break;
            case 'checkbox':
                return FieldFormatter::formatFieldBoxes($field, $value, $block, $context);
                break;
            case 'fileupload':
                return FieldFormatter::formatFieldUpload($field, $value, $block, $context);
                break;
            case 'upload':
                return FieldFormatter::formatFieldUpload($field, $value, $block, $context);
                break;
            case 'selector':
                return FieldFormatter::formatFieldSelector($field, $value, $block, $context);
                break;
            case 'select':
                return FieldFormatter::formatFieldSelect($field, $value, $block, $context);
                break;
            case 'radio_group':
                return FieldFormatter::formatFieldRadioGroup($field, $value, $block, $context);
                break;
            default:
                return '';
        }
    }
}