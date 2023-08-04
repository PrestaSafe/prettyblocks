<?php

use PrestaSafe\PrettyBlocks\Core\FieldCore;
use PrestaSafe\PrettyBlocks\Core\PrettyBlocksField;

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
class PrettyBlocksModel extends ObjectModel
{
    public $id_prettyblocks;
    public $instance_id;
    public $code;
    public $default_params;
    public $template;
    public $config;
    public $state;
    public $name;
    public $zone_name;
    public $position;
    public $id_shop;
    public $id_lang;
    public $date_add;
    public $date_upd;

    public $configFields = [];
    public $stateFields = [];
    public $count = 0;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'prettyblocks',
        'primary' => 'id_prettyblocks',
        'fields' => [
            'config' => ['type' => self::TYPE_STRING, 'validate' => 'isJson'],
            'code' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            'default_params' => ['type' => self::TYPE_STRING, 'validate' => 'isJson'],
            'template' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            // multilang
            'name' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_lang' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],

            // multishop
            'instance_id' => ['type' => self::TYPE_STRING,  'validate' => 'isCleanHtml'],
            'state' => ['type' => self::TYPE_SQL, 'validate' => 'isJson'],
            'zone_name' => ['type' => self::TYPE_STRING,  'validate' => 'isCleanHtml'],
            'position' => ['type' => self::TYPE_INT,  'validate' => 'isInt'],
            'date_add' => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE,  'validate' => 'isDate'],
        ],
    ];

    /**
     * Load Block by Code
     *
     * @param string $code
     *
     * @return array
     */
    public static function loadBlock($code)
    {
        $blocks = self::getBlocksAvailable();

        return (isset($blocks[$code])) ? $blocks[$code] : [];
    }

    /**
     * delete the model
     *
     * @return bool
     */
    public function delete()
    {
        return parent::delete()
            && $this->removeConfig();
    }

    /**
     * Remove related lines from configuration table
     *
     * @return bool
     */
    public function removeConfig()
    {
        $id = (int) $this->id_prettyblocks;

        $key1 = $id . '\_%\_CONFIG';
        $key2 = $id . '\_DEFAULT\_PARAMS';
        $key3 = $id . '\_TEMPLATE';

        $sql = '
            SELECT name
            FROM ' . _DB_PREFIX_ . 'configuration
            WHERE name LIKE "' . pSQL($key1) . '" 
            OR name LIKE "' . pSQL($key2) . '"
            OR name LIKE "' . pSQL($key3) . '"
        ';

        $configLines = Db::getInstance()->executeS($sql);

        if ($configLines) {
            foreach ($configLines as $configLine) {
                if (!Configuration::deleteByName($configLine['name'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Display blocks for one zone
     *
     * @param zone_name|string
     * @param context|string : back or front (different results)
     * @param id_lang|int
     * @param id_shop|int
     *
     * @return array
     */
    public static function getInstanceByZone($zone_name, $context = 'back', $id_lang = null, $id_shop = null)
    {
        $contextPS = Context::getContext();

        $id_lang = (!is_null($id_lang)) ? (int) $id_lang : $contextPS->language->id;
        $id_shop = (!is_null($id_shop)) ? (int) $id_shop : $contextPS->shop->id;
        $psc = new PrestaShopCollection('PrettyBlocksModel', $id_lang);

        $psc->where('zone_name', '=', $zone_name);
        $psc->where('id_shop', '=', (int) $id_shop);
        $psc->where('id_lang', '=', (int) $id_lang);

        $psc->orderBy('position');
        $blocks = [];
        foreach ($psc->getResults() as $res) {
            if ($res) {
                $block = $res->mergeStateWithFields();
                if ($context == 'front') {
                    $block = (new BlockPresenter())->present($res->mergeStateWithFields($id_lang));
                }
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    /**
     * saveConfigField
     *
     * @param mixed $name
     * @param mixed $new_value
     *
     * @return bool
     */
    public function saveConfigField($name, $new_value)
    {
        if (isset($this->configFields[$name])) {
            $newField = $this->configFields[$name]->setAttribute('new_value', $new_value)->compile();
            $jsonConfig = json_decode($this->config, true);
            if (!is_null($jsonConfig)) {
                $json = [];
            }
            $json[$name] = $newField;
            $this->config = json_encode($json, true);

            return $this->save();
        }

        return false;
    }

    /**
     * saveStateField
     *
     * @param mixed $index
     * @param mixed $name
     * @param mixed $new_value
     *
     * @return bool
     */
    public function saveStateField($index, $name, $new_value)
    {
        // get state in json, replace it if exist and save model.
        if (isset($this->stateFields[$index][$name])) {
            $newField = $this->stateFields[$index][$name]->setAttribute('new_value', $new_value)->compile();
            $jsonConfig = json_decode($this->state, true);
            if (is_null($jsonConfig)) {
                $jsonConfig = [];
            }
            $jsonConfig[$index][$name] = $newField;
            $this->state = json_encode($jsonConfig, true);

            return $this->save();
        }

        return false;
    }

    /**
     * Mapped block repeater with states.
     */
    public function mergeStateWithFields()
    {
        $context = Context::getContext();
        // $id_lang = ($id_lang !== null) ? (int)$id_lang : $context->language->id;
        $states = json_decode($this->state, true);
        $block = $this->loadBlock($this->code);

        $repeaterDefault = (isset($block['repeater']['groups'])) ? $block['repeater']['groups'] : [];
        $block['states_json'] = $states;
        $block['config_json'] = json_decode($this->config, true);
        $block['state_to_push'] = $this->_formatDefautStateFromBlock($repeaterDefault);
        $block['instance_id'] = $this->instance_id;
        $block['id_prettyblocks'] = $this->id;
        $block['id_shop'] = $this->id_shop;
        $block['id_lang'] = $this->id_lang;
        $block['code'] = $this->code;
        $block['settings'] = $this->_formatGetConfig($block);
        $block['settings_formatted'] = $this->_formatConfig($block, 'back');

        $block['repeater_db'] = $this->_formatRepeaterDb($states, $repeaterDefault);

        // format state for front
        $block['states'] = $this->_formatStateForFront($states, $repeaterDefault, $block);

        $block['formatted'] = $this->formatBlock($block);
        $block['extra'] = [];
        $block['templateSelected'] = $this->_getTemplateSelected($block);

        $extraContent = Hook::exec('beforeRendering' . Tools::toCamelCase($this->code), [
            'block' => $block,
            'settings' => $block['settings'],
        ], null, true);
        $res = [];
        if (is_array($extraContent)) {
            foreach ($extraContent as $moduleName => $additionnalFormFields) {
                $res = $additionnalFormFields;
            }
        }

        $block['extra'] = $res;
        $block['templates'] = $this->_getBlockTemplate($block);

        return $block;
    }

    public function _formatConfig($block, $context = 'front')
    {
        $formatted = [];
        $this->assignFields($block, $context);

        foreach ($this->configFields as $name => $field) {
            $formatted[$name] = (new FieldCore($field))->compile();
        }
        // is settings_formatted section block
        $formatted['templates'] = $this->_getBlockTemplate($block);
        $formatted['templateSelected'] = $this->_getTemplateSelected($block);
        $formatted['default'] = $this->_getDefaultParams($block);

        return $formatted;
    }

    /**
     * set Fields config fields to the model
     */
    public function setConfigFields($fields)
    {
        $this->configFields = $fields;
    }

    /**
     * get Fields config fields to the model
     *
     * @param block|array
     * @param context|string : back or front (different results)
     * @param force_values|bool : force values from db
     *
     * @return $this
     */
    public function assignFields($block = false, $context = 'front', $force_values = false)
    {
        ++$this->count;
        if (!$block) {
            $block = $this->mergeStateWithFields();
        }

        $fieldCore = new PrettyBlocksField($block);
        $this->configFields = $fieldCore->getConfigFields();
        $this->stateFields = $fieldCore->getStatesFields();

        return $this;
    }

    /**
     * generate field config in json
     *
     * @param returnJson|bool : return json or array
     */
    public function generateJsonConfig($returnJson = true)
    {
        $output = [];
        foreach ($this->configFields as $key => $field) {
            $output[$key] = $field->compile();
        }

        if ($returnJson) {
            return json_encode($output, true);
        }

        return $output;
    }

    /**
     * get the selected template
     */
    private function _getTemplateSelected($block)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = Tools::strtoupper($id_prettyblocks . '_template');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $defaultTemplate = (isset($block['templates']['default'])) ? 'default' : 'welcome';
        if ($this->template && isset($block['templates'][$this->template])) {
            $defaultTemplate = $this->template;
        }

        return $defaultTemplate;
    }

    /**
     * Set template chosen in Vue App
     */
    private function _setConfigTemplate($block, $template_name)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = Tools::strtoupper($id_prettyblocks . '_template');

        return Configuration::updateValue($key, $template_name, false, null, (int) $block['id_shop']);
    }

    /**
     * return $block.states in front
     */
    private function _formatStateForFront($state, $repeatedFields, $block)
    {
        $empty_state = [];
        foreach ($state as $s) {
            $formatted = [];
            if (empty($s)) {
                return $formatted;
            }
            $key = key($state);
            foreach ($s as $fieldName => $value) {
                $formatted[$fieldName] = (new FieldCore($value))->getFrontValue();
            }
            $empty_state[$key] = $formatted;
            next($state);
        }

        return $empty_state;
    }

    /**
     * update the model configuration
     *
     * @param string $stateRequest
     *
     * @return bool
     */
    public function updateConfig($stateRequest)
    {
        $fields = [];
        $stateRequest = json_decode($stateRequest, true);
        $fieldsRequest = array_filter($stateRequest, function ($field) {
            return isset($field['type']);
        });

        foreach ($fieldsRequest as $key => $field) {
            $obj = (new FieldCore($field))->setAttribute('new_value', $field['value']);
            $fields[$key] = $obj;
        }
        $this->setConfigFields($fields);
        $this->config = $this->generateJsonConfig();
        $template_name = pSQL($stateRequest['templateSelected']);
        $this->setCurrentTemplate($template_name);
        $this->setDefaultParams($stateRequest['default']);

        return $this->save();
    }

    /**
     * update default template
     *
     * @param string
     *
     * @return void
     */
    public function setCurrentTemplate($tpl)
    {
        $this->template = $tpl;
    }

    /**
     * update default params
     *
     * @param block|array
     * @param json
     */
    public function setDefaultParams($params)
    {
        $this->default_params = json_encode($params, true);
    }

    /**
     * get default params
     *
     * @return array
     */
    public function getDefaultParams()
    {
        $data = json_encode([], true);
        if (\Validate::isJson($this->default_params)) {
            $data = $this->default_params;
        }

        return json_decode($data, true);
    }

    /**
     * Compile sass files based on hook ActionQueueSassCompile
     *
     * @return void
     */
    private static function _compileSass()
    {
        $sass_hook = HelperBuilder::hookToArray('ActionQueueSassCompile');
        foreach ($sass_hook as $options) {
            $compiler = new PrettyBlocksCompiler();
            if (isset($options['import_path'])) {
                $compiler->setImportPaths($options['import_path']);
            }
            if (isset($options['entries'])) {
                $compiler->setEntries($options['entries']);
            }
            if (isset($options['out'])) {
                $compiler->setOuput($options['out']);
            }
            $compiler->compileAndWrite();
        }
    }

    /**
     * update theme settings
     *
     * @param array $stateRequest
     *
     * @return void
     */
    public static function updateThemeSettings($stateRequest)
    {
        $formatted = [];
        foreach ($stateRequest as $tabs) {
            foreach ($tabs as $name => $field) {
                if (!isset($field['type'])) {
                    continue;
                }
                switch ($field['type']) {
                    case 'text':
                        FieldUpdator::updateFieldText($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'textarea':
                        FieldUpdator::updateFieldText($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'color':
                        FieldUpdator::updateFieldText($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'checkbox':
                        FieldUpdator::updateFieldBoxes($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'radio':
                        FieldUpdator::updateFieldBoxes($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'fileupload':
                        FieldUpdator::updateFieldUpload($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'upload':
                        FieldUpdator::updateFieldUpload($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'selector':
                        FieldUpdator::updateFieldSelector($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'editor':
                        FieldUpdator::updateFieldEditor($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'select':
                        FieldUpdator::updateFieldSelect($name, $field['value'], $block = false, '_settings');
                        break;
                    case 'radio_group':
                        FieldUpdator::updateFieldRadioGroup($name, $field['value'], $block = false, '_settings');
                        break;
                }
            }
        }
        self::_compileSass();
    }

    /**
     * get blocks templates by using hook actionExtendBlockTemplate{BlockCode}
     *
     * @return array
     */
    private function _getBlockTemplate(&$block)
    {
        $hookName = 'actionExtendBlockTemplate' . Tools::toCamelCase($this->code);
        $extraContent = Hook::exec(
            $hookName,
            [],
            null,
            true
        );
        $res = [];
        if (is_array($extraContent)) {
            foreach ($extraContent as $moduleName => $additionnalFormFields) {
                if (!is_array($extraContent)) {
                    continue;
                }
                foreach ($extraContent as $formField) {
                    $res = $formField;
                }
            }
        }
        if (!isset($block['templates'])) {
            $block['templates'] = [];
        }

        return $block['templates'] + $res;
    }

    /**
     * _getDefaultParams
     * return default template if not exist
     * prettyblocks:views/templates/blocks/welcome.tpl
     *
     * @param mixed $block
     *
     * @return void
     */
    private function _getDefaultParams($block)
    {
        $options = [
            'container' => true,
            'load_ajax' => false,
            'bg_color' => '',
        ];
        $defaultParams = $this->getDefaultParams();
        if (!$defaultParams) {
            return $options;
        }

        return $defaultParams;
    }

    /**
     * _formatGetConfig
     * get field with front value only
     *
     * @param mixed $block
     * @param mixed $context
     *
     * @return array
     */
    private function _formatGetConfig($block, $context = 'front')
    {
        $formatted = [];
        $fields = $this->_formatConfig($block, $context);

        // get only fields with type
        $fields = array_filter($fields, function ($field) {
            return isset($field['type']);
        });
        foreach ($fields as $name => $field) {
            $formatted[$name] = (new FieldCore($field))->getFrontValue();
        }
        $formatted['templates'] = $this->_getBlockTemplate($block);
        $formatted['templateSelected'] = $this->_getTemplateSelected($block);
        $formatted['default'] = $this->_getDefaultParams($block);

        return $formatted;
    }

    /**
     * Format config for front office
     */
    private function _formatRepeaterDb($state, $repeaterDefault)
    {
        $res = [];

        foreach ($state as $s) {
            if (!$s || $state === null) {
                return $res;
            }

            foreach ($s as $key => $value) {
                //  TODO Fix key with no default value
                if (isset($repeaterDefault[$key])) {
                    $repeaterDefault[$key]['value'] = $value['value'] ?? '';
                } elseif (isset($repeaterDefault[$key]['default'])) {
                    $repeaterDefault[$key]['value'] = $repeaterDefault[$key]['default'];
                } elseif (!isset($repeaterDefault[$key]['value'])) {
                    $repeaterDefault[$key]['value'] = '';
                }
            }
            $index = key($state);
            $res[$index] = $repeaterDefault;
            next($state);
        }

        return $res;
    }

    /**
     * Format the default block to push an emtpy element in state
     */
    private function _formatDefautStateFromBlock($block)
    {
        foreach ($block as $field => $value) {
            $block[$field]['value'] = $block[$field]['default'] ?? '';
        }

        return $block;
    }

    /**
     * List all block available from modules
     * Get Hook ActionRegisterBlock
     *
     * @return array
     */
    public static function getBlocksAvailable()
    {
        $modules = Hook::exec('ActionRegisterBlock', $hook_args = [], $id_module = null, $array_return = true);

        $blocks = [];
        foreach ($modules as $data) {
            if (!isset($data[0])) {
                $data[0] = $data;
            }
            foreach ($data as $block) {
                $blocks[$block['code']] = $block;
                // formatted for LeftPanel.vue
                $blocks[$block['code']]['formatted'] = self::formatBlock($block);
            }
        }

        return $blocks;
    }

    /**
     * Format block for Interface
     *
     * @return array
     */
    private static function formatBlock($block)
    {
        $formatted = [];
        $id = (isset($block['id_prettyblocks'])) ? '-' . $block['id_prettyblocks'] : '';
        $formatted['id'] = $block['code'] . $id;
        $formatted['id_prettyblocks'] = $block['id_prettyblocks'] ?? '';
        $formatted['instance_id'] = $block['instance_id'] ?? Tools::passwdGen(8, 'NUMERIC');
        $formatted['icon'] = $block['icon'] ?? 'PhotographIcon';
        $formatted['icon_path'] = $block['icon_path'] ?? '';
        $formatted['module'] = $block['code']; // todo register module name
        $formatted['title'] = $block['name'];
        // dump($block);

        // if nameFrom params is present
        if (isset($block['nameFrom'], $block['settings_formatted'][$block['nameFrom']]['value'])) {
            $formatted['title'] = $block['settings_formatted'][$block['nameFrom']]['value'];
        }
        $formatted['is_parent'] = true;
        $formatted['is_child'] = false;
        $formatted['need_reload'] = $block['need_reload'] ?? true;
        $formatted['can_repeat'] = (isset($block['repeater'])) ? true : false;
        if (isset($block['repeater_db'])) {
            foreach ($block['repeater_db'] as $key => $data) {
                $numeric = (int) $formatted['id_prettyblocks'] . '-' . $key;
                $title = ($block['repeater']['name']) ? $block['repeater']['name'] : 'Element';
                if (isset($block['repeater']['nameFrom'], $data[$block['repeater']['nameFrom']]['value'])) {
                    $title = $data[$block['repeater']['nameFrom']]['value'];
                }
                $formatted['children'][] = [
                    'id' => $numeric,
                    'is_child' => true,
                    'is_parent' => false,
                    'id_prettyblocks' => $formatted['id_prettyblocks'],
                    'type' => $data['type'] ?? '',
                    'title' => $title,
                    'icon' => $data['icon'] ?? 'SquaresPlusIcon',
                    'can_repeat' => $formatted['can_repeat'],
                    'need_reload' => $formatted['need_reload'],
                ];
            }
        }

        return $formatted;
    }

    /**
     * Format the front result for MagicField
     * ex {id} - {name} will return 2 - Home for cat id 2
     *
     * @return string
     */
    public static function formatFrontSelector($collection, $selector = null)
    {
        $selectorregex = ($selector !== null) ? $selector : '{primary} - {name}';
        // $regex = "/\{[a-zA-Z]+\}/";
        $regex = "/\{.*?\}/";
        preg_match_all($regex, $selectorregex, $test);
        $terms = $test[0];
        $formatted = $selectorregex;
        foreach ($terms as $term) {
            $nbT = str_replace('{', '', $term);
            $nbT = str_replace('}', '', $nbT);

            if (isset($collection->{$nbT})) {
                $formatted = str_replace($term, $collection->{$nbT}, $formatted);
            }
        }

        return $formatted;
    }

    /**
     * registerBlockToZone - Insert a block in a zone
     *
     * @param $zone_name String
     * @param $block_name String
     *
     * @return PrettyBlocksModel
     */
    public static function registerBlockToZone($zone_name, $block_code, $id_lang = null, $id_shop = null)
    {
        $contextPS = Context::getContext();
        $id_lang = ($id_lang !== null) ? (int) $id_lang : $contextPS->language->id;
        $id_shop = ($id_shop !== null) ? (int) $id_shop : $contextPS->shop->id;

        $model = new PrettyBlocksModel(null, $id_lang, $id_shop);
        $model->zone_name = $zone_name;
        $model->code = $block_code;
        $model->name = $block_code;
        $array = [];
        $model->state = json_encode($array, true);
        $model->instance_id = uniqid();
        $model->id_shop = $id_shop;
        $model->save();

        $block = $model->mergeStateWithFields();

        if (isset($block['insert_default_values']) && $block['insert_default_values'] === true) {
            // force default values
            $model->setDefaultConfigValues($block);
            // push one state
            $state_to_push = $block['state_to_push'];
            $state_db = json_decode($model->state, true);
            $state_db[1] = (object) $state_to_push;
            $encoded = json_encode($state_db);
            $model->state = $encoded;
            $model->save();
        }

        return $block;
    }

    /**
     * setDefaultConfigValues set default config values
     *
     * @param $block array
     *
     * @return bool
     */
    private function setDefaultConfigValues($block)
    {
        $this->assignFields($block, 'back', true);

        $json = $this->generateJsonConfig();

        $this->config = $json;
        $this->save();
    }

    /**
     * override add method
     *
     * @param bool $auto_date
     * @param bool $null_values
     *
     * @return void
     */
    public function add($auto_date = true, $null_values = false)
    {
        $this->position = (int) DB::getInstance()->getValue('SELECT MAX(position)  FROM `' . _DB_PREFIX_ . 'prettyblocks`') + 1;
        parent::add();
    }

    /**
     * Get Theme Settings
     *
     * @param bool $with_tabs
     * @param string $context
     *
     * @return array
     */
    public static function getThemeSettings($with_tabs = true, $context = 'front')
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

    /**
     * Set Theme Settings
     *
     * @param string $name
     * @param array $params
     * @param string $context (back of front)
     *
     * @return array
     */
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
}
