<?php

use PrestaSafe\PrettyBlocks\Core\FieldCore;
use PrestaSafe\PrettyBlocks\Core\PrettyBlocksField;

class PrettyBlocksMigrate
{
    /**
     * add field template to database
     *
     * @return bool
     */
    public static function addTemplateField()
    {
        $sql = [];
        $res = true;
        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'prettyblocks` ADD `template` longtext DEFAULT NULL AFTER `config`;';
        $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'prettyblocks` ADD `default_params` longtext DEFAULT NULL AFTER `config`;';
        foreach ($sql as $query) {
            $res &= Db::getInstance()->execute($query);
        }

        return $res;
    }

    public static function migrateConfig()
    {
        if (!self::columnExists('prettyblocks', 'template')
            && !self::columnExists('prettyblocks', 'default_params')) {
            self::addTemplateField();
        }
        $langs = Language::getLanguages();
        $res = true;
        $fields = [];
        foreach ($langs as $lang) {
            $lang_id = $lang['id_lang'];
            $blocks = (new PrestaShopCollection('PrettyBlocksModel', $lang_id))->getAll();
            // get old config
            foreach ($blocks as $model) {
                $block = $model->mergeStateWithFields();
                $config = $block['config']['fields'] ?? [];
                if (is_array($config) && count($config) > 0) {
                    foreach ($config as $field => $value) {
                        $formatted[$field] = self::_formatFieldConfigFront($field, $value, $block, 'back');
                    }
                }
                // convert in fields and save
                foreach ($formatted as $name => $data) {
                    $field = (new PrettyBlocksField($block))
                        ->setKey($name)
                        ->setNewValue($data)
                        ->get();
                    $fields[$name] = $field;
                }
                $model->setConfigFields($fields);
                $model->config = $model->generateJsonConfig();

                // moving template to model
                $model->setCurrentTemplate(pSQL(self::_getTemplateSelected($block)));

                // moving default params to model
                $model->setDefaultParams(self::_getDefaultParams($block));

                $model->save();

                // destroy configuration
                $res &= $model->removeConfig();
            }
        }

        return $res;
    }

    public static function columnExists($tableName, $columnName)
    {
        $tableName = _DB_PREFIX_ . $tableName;
        $sql = "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'";
        $result = Db::getInstance()->executeS($sql);

        return !empty($result);
    }

    public static function tableExists($tableName)
    {
        $tableName = _DB_PREFIX_ . $tableName;
        $sql = "SHOW TABLES LIKE '$tableName'";
        $result = Db::getInstance()->executeS($sql);

        // Retourne true si le tableau de résultat n'est pas vide, sinon false.
        return !empty($result);
    }

    /**
     * Migrate lang table
     * for version 3.0.0
     */
    public static function migrateLangTable()
    {
        if (!self::tableExists('prettyblocks_lang')) {
            return true;
        }
        if (!self::columnExists('prettyblocks', 'id_shop')
            && !self::columnExists('prettyblocks', 'id_lang')
            && !self::columnExists('prettyblocks', 'state')) {
            $sql = '
                ALTER TABLE ' . _DB_PREFIX_ . 'prettyblocks
                ADD COLUMN id_shop int(11) DEFAULT NULL,
                ADD COLUMN id_lang int(11) DEFAULT NULL,
                ADD COLUMN state longtext DEFAULT NULL;
            ';
            Db::getInstance()->execute($sql);
        }

        $blocks_lang = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'prettyblocks_lang');
        foreach ($blocks_lang as $block) {
            $existingBlock = new PrettyBlocksModel($block['id_prettyblocks']);
            $newBlocks = new PrettyBlocksModel();
            $newBlocks->instance_id = $existingBlock->instance_id;
            $newBlocks->id_shop = (int) $block['id_shop'];
            $newBlocks->id_lang = (int) $block['id_lang'];
            $newBlocks->state = $block['state'];
            $newBlocks->code = $existingBlock->code;
            $newBlocks->zone_name = $existingBlock->zone_name;
            $newBlocks->position = $existingBlock->position;

            $newBlocks->name = $existingBlock->name;
            $newBlocks->config = $existingBlock->config;
            $newBlocks->template = $existingBlock->template;
            $newBlocks->default_params = $existingBlock->default_params;
            $newBlocks->save();

            $existingBlock->delete();
        }

        $sql = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'prettyblocks_lang';
        Db::getInstance()->execute($sql);

        return true;
    }

    /**
     * migrateSettings
     * migrate settings from config to database
     *
     * @return bool
     */
    public static function migrateSettings()
    {
        $key = '%\_SETTINGS';

        $sql = '
            SELECT name, value
            FROM ' . _DB_PREFIX_ . 'configuration
            WHERE name LIKE "' . pSQL($key) . '"
            AND name != "PS_REWRITING_SETTINGS"
        ';
        // get settings value from config values
        $configLines = Db::getInstance()->executeS($sql);
        $settings_value = [];
        if ($configLines) {
            foreach ($configLines as $configLine) {
                $setting = str_replace('_SETTINGS', '', $configLine['name']);
                $setting = strtolower($setting);
                $settings_value[$setting] = $configLine['value'];
            }
        }

        // get settings fields:

        $settings_on_hooks = HelperBuilder::hookToArray('ActionRegisterThemeSettings');
        $res = [];

        foreach ($settings_on_hooks as $key => $field) {
            $fieldCore = (new FieldCore($field));
            if (isset($settings_value[$key])) {
                $fieldCore->setAttribute('value', $settings_value[$key]);
            }
            $res[$key] = $fieldCore->compile();
        }

        $theme_name = Context::getContext()->shop->theme_name;
        $can_delete_settings = false;
        // if (!self::tableExists('prettyblocks_settings')) {
        //     $prettyblocks = \Module::getInstanceByName('prettyblocks');
        //     $prettyblocks->makeSettingsTable();
        // }

        foreach (Shop::getShops() as $shop) {
            $id_shop = (int) $shop['id_shop'];

            // get settings from database
            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'prettyblocks_settings WHERE theme_name = "' . pSQL($theme_name) . '" AND id_shop = ' . (int) $id_shop;
            $row = Db::getInstance()->getRow($sql);

            if ($row) {
                // if settings exists, update
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'prettyblocks_settings SET settings = "' . pSQL(json_encode($res, true)) . '", profile = "Theme ' . pSQL($theme_name) . '" WHERE theme_name = "' . pSQL($theme_name) . '" AND id_shop = ' . (int) $id_shop;
                $result = Db::getInstance()->execute($sql);
            } else {
                // if settings not exists, create
                $data = [
                    'theme_name' => pSQL($theme_name),
                    'settings' => pSQL(json_encode($res, true)),
                    'id_shop' => (int) $id_shop,
                    'profile' => 'Theme ' . pSQL($theme_name),
                ];
                $result = Db::getInstance()->insert('prettyblocks_settings', $data);
            }

            if ($result) {
                $can_delete_settings = true;
            }
        }
        if ($can_delete_settings) {
            foreach ($configLines as $configLine) {
                Configuration::deleteByName($configLine['name']);
            }
        }

        return true;
    }

    /**
     * getConfigurationSettings
     *
     * @param mixed $with_tabs
     * @param mixed $context
     *
     * @return array
     */
    public static function getConfigurationSettings($with_tabs = true, $context = 'front')
    {
        $theme_settings = Hook::exec('ActionRegisterThemeSettings', [], null, true);
        $res = [];
        $no_tabs = [];
        foreach ($theme_settings as $settings) {
            foreach ($settings as $name => $params) {
                $tab = $params['tab'] ?? 'general';
                $params = self::_setThemeFieldValue($name, $params, $context);
                $res[$tab][$name] = $params;
                $no_tabs[$name] = $params['value'] ?? false;
            }
        }
        if (!$with_tabs) {
            return $no_tabs;
        }

        return $res;
    }

    private static function _setThemeFieldValue($name, $params, $context)
    {
        $params['value'] = self::_formatSettingsField($name, $params['type'], $params, $context, false);

        return $params;
    }

    /**
     * Format a field for settings
     *
     * @param string $name
     * @param string $type
     * @param array $params
     * @param string $context (back of front)
     * @param bool|array $block
     *
     * @return any
     */
    private static function _formatSettingsField($name, $type, $params, $context, $block = false)
    {
        $class = new FieldFormatter();
        $class::setSuffix('_settings');

        switch ($type) {
            case 'editor':
                return $class::formatFieldText($name, $params, $block, $context);
                break;
            case 'text':
                return $class::formatFieldText($name, $params, $block, $context);
                break;
            case 'textarea':
                return $class::formatFieldText($name, $params, $block, $context);
                break;
            case 'color':
                return $class::formatFieldText($name, $params, $block, $context);
                break;
            case 'radio':
                return $class::formatFieldBoxes($name, $params, $block, $context);
                break;
            case 'checkbox':
                return $class::formatFieldBoxes($name, $params, $block, $context);
                break;
            case 'fileupload':
                return $class::formatFieldUpload($name, $params, $block, $context);
                break;
            case 'upload':
                return $class::formatFieldUpload($name, $params, $block, $context);
                break;
            case 'selector':
                return $class::formatFieldSelector($name, $params, $block, $context);
                break;
            case 'select':
                return $class::formatFieldSelect($name, $params, $block, $context);
                break;
            case 'radio_group':
                return $class::formatFieldRadioGroup($name, $params, $block, $context);
                break;
            default:
                return '';
        }
    }

    /**
     * Get default params in Configuration
     *
     * @param array $block
     *
     * @return array
     */
    private static function _getDefaultParams($block)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = Tools::strtoupper($id_prettyblocks . '_default_params');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $options = [
            'container' => true,
            'load_ajax' => false,
            'bg_color' => '',
        ];
        $defaultParams = Configuration::get($key);
        if (!$defaultParams) {
            return $options;
        }

        return json_decode($defaultParams, true);
    }

    /**
     * get template in Configuration
     *
     * @param array $block
     *
     * @return string
     */
    private static function _getTemplateSelected($block)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = Tools::strtoupper($id_prettyblocks . '_template');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $default_tpl = (isset($block['templates']['default'])) ? 'default' : 'welcome';
        $currentTemplate = Configuration::get($key, null, null, (int) $block['id_shop']);
        if ($currentTemplate !== false && isset($block['templates'][$currentTemplate])) {
            return $currentTemplate;
        }

        return $default_tpl;
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
