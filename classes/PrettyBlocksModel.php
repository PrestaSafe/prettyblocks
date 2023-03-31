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
class PrettyBlocksModel extends ObjectModel
{
    public $id_prettyblocks;
    public $instance_id;
    public $code;
    public $config;
    public $state;
    public $name;
    public $zone_name;
    public $position;

    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'prettyblocks',
        'primary' => 'id_prettyblocks',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            'config' => ['type' => self::TYPE_STRING, 'validate' => 'isJson'],
            'code' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            // multilang
            'name' => ['type' => self::TYPE_STRING,   'validate' => 'isCleanHtml'],
            // multishop
            'instance_id' => ['type' => self::TYPE_STRING,  'validate' => 'isCleanHtml'],
            'state' => ['type' => self::TYPE_SQL, 'validate' => 'isJson',  'lang' => true],
            'zone_name' => ['type' => self::TYPE_STRING,  'validate' => 'isCleanHtml'],
            'position' => ['type' => self::TYPE_INT,  'validate' => 'isInt'],
            'date_add' => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE,  'validate' => 'isDate'],
        ],
    ];

    public static function loadBlock($code)
    {
        $blocks = self::getBlocksAvailable();

        return (isset($blocks[$code])) ? $blocks[$code] : [];
    }

    public function delete()
    {
        return parent::delete()
            && $this->removeConfig();
    }

    /**
     * Remove related lines from configuration table
     */
    private function removeConfig()
    {
        $id = (int) $this->id_prettyblocks;

        $key1 = $id . '\_%\_CONFIG';
        $key2 = $id . '\_DEFAULT\_PARAMS';
        $key3 = $id . '\_TEMPLATE';

        $configLines = Db::getInstance()->executeS('
            SELECT name
            FROM ' . _DB_PREFIX_ . 'configuration
            WHERE name LIKE "' . pSQL($key1) . '"
            OR name LIKE "' . pSQL($key2) . '"
            OR name LIKE "' . pSQL($key3) . '"
        ');

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
     * @param context|string : back of front (different results)
     * @param id_lang|int
     * @param id_shop|int
     *
     * @return array
     */
    public static function getInstanceByZone($zone_name, $context = 'back', $id_lang = null, $id_shop = null)
    {
        $context = Context::getContext();
        $id_lang = (!is_null($id_lang)) ? (int) $id_lang : $context->language->id;
        $id_shop = (!is_null($id_shop)) ? (int) $id_shop : $context->shop->id;
        $psc = new PrestaShopCollection('PrettyBlocksModel', $id_lang);
        
        $psc->where('zone_name', '=', $zone_name);
        // $psc->where('l.id_lang', '=', (int) $id_lang);
        // $psc->where('a0.id_shop', '=', (int) $id_shop);

        $psc->orderBy('position');
        $psc->getResults();
        dump($psc->query->__toString());
        die();
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
     * Mapped block repeater with states.
     */
    public function mergeStateWithFields()
    {
        $context = Context::getContext();
        // $id_lang = ($id_lang !== null) ? (int)$id_lang : $context->language->id;
        $state = json_decode($this->state, true);
        $block = $this->loadBlock($this->code);
        $repeaterDefault = (isset($block['repeater']['groups'])) ? $block['repeater']['groups'] : [];
        $block['state_to_push'] = $this->_formatDefautStateFromBlock($repeaterDefault);
        $block['instance_id'] = $this->instance_id;
        $block['id_prettyblocks'] = $this->id;
        $block['code'] = $this->code;
        $block['settings'] = $this->_formatGetConfig($block);
        $block['settings_formatted'] = $this->_formatGetConfigForApp($block, 'back');

        $block['repeater_db'] = $this->_formatRepeaterDb($state, $repeaterDefault);

        // format state for front
        $block['states'] = $this->_formatStateForFront($state, $repeaterDefault, $block);

        $block['formatted'] = $this->formatBlock($block);
        $block['extra'] = [];

        $block['templateSelected'] = $this->_getTemplateSelected($block);
        // dump('beforeRendering'.Tools::toCamelCase($this->code));

        $extraContent = Hook::exec('beforeRendering' . Tools::toCamelCase($this->code), [
            'block' => $block,
            'settings' => $block['settings'],
        ], null, true);
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

        $block['extra'] = $res;
        $block['templates'] = $this->_getBlockTemplate($block);

        return $block;
    }

    /**
     * get the selected template
     */
    private function _getTemplateSelected($block)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = Tools::strtoupper($id_prettyblocks . '_template');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $default_tpl = (isset($block['templates']['default'])) ? 'default' : 'welcome';
        $defaultTemplate = Configuration::get($key);
        if (!$defaultTemplate) {
            return $default_tpl;
        }

        return $defaultTemplate;
    }

    /**
     * Set template chosen in Vue App
     */
    private function _setConfigTemplate($id_prettyblocks, $template_name)
    {
        $key = Tools::strtoupper($id_prettyblocks . '_template');

        return Configuration::updateValue($key, $template_name);
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

            foreach ($s as $fieldName => $value) {
                $formatted[$fieldName] = $this->_formatFieldStateFront($fieldName, $value, $repeatedFields);
            }
            $empty_state[] = $formatted;
        }

        return $empty_state;
    }

    private function _formatFieldStateFront($fieldName, $value, $repeatedFields)
    {
        $field = $repeatedFields[$fieldName];

        if ($field['type'] == 'fileupload') {
            return StateFormatter::formatFieldUpload($value);
        }

        if ($value['type'] == 'selector') {
            return StateFormatter::formatFieldSelector($value);
        }

        // return text value for field type text or textarea
        return StateFormatter::formatFieldDefault($value);
    }

    public function updateConfig($stateRequest)
    {
        $block = $this->mergeStateWithFields();
        $config = $this->_formatGetConfigForApp($block);
        $this->_formatUpdateConfig($config, $stateRequest, $block);

        return true;
    }

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
        // dump($stateRequest);

        self::_compileSass();
    }

    /**
     * Update configuration block settings
     */
    private function _formatUpdateConfig($config, $stateRequest, $block)
    {
        foreach ($config as $name => $field) {
            $this->_updateFieldValue($stateRequest, $name, $field, $block);
        }

        $template_name = pSQL($stateRequest['templateSelected']);
        $this->_updateDefaultParams($block, $stateRequest);
        $this->_setConfigTemplate($block['id_prettyblocks'], $template_name);
    }

    /**
     * Update default params of block such as container, load_ajax, bg_color
     */
    private function _updateDefaultParams($block, $stateRequest)
    {
        $key = Tools::strtoupper($block['id_prettyblocks'] . '_default_params');
        Configuration::updateValue($key, json_encode($stateRequest['default'], true));
    }

    /**
     * Update value and save Configuration
     */
    private function _updateFieldValue($stateRequest, $name, $field, $block)
    {
        if (!isset($field['type'])) {
            return false;
        }

        switch ($field['type']) {
            case 'text':
                FieldUpdator::updateFieldText($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'textarea':
                FieldUpdator::updateFieldText($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'color':
                FieldUpdator::updateFieldText($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'checkbox':
                FieldUpdator::updateFieldBoxes($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'radio':
                FieldUpdator::updateFieldBoxes($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'fileupload':
                FieldUpdator::updateFieldUpload($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'upload':
                FieldUpdator::updateFieldUpload($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'selector':
                FieldUpdator::updateFieldSelector($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'select':
                FieldUpdator::updateFieldSelect($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
            case 'editor':
                FieldUpdator::updateFieldEditor($name, ($stateRequest[$name]['value']) ?? false, $block);
                break;
        }

        return true;
    }

    /**
     * Formatted data config for app (state like)
     */
    private function _formatGetConfigForApp($block, $context = 'front')
    {
        $config = ($block['config']['fields']) ?? [];
        $formatted = [];
        $config = ($block['config']['fields']) ?? [];
        if (is_array($config) && count($config) > 0) {
            $values = $this->_formatGetConfig($block, $context);
            foreach ($config as $name => $field) {
                // set the value $block['settings_formatted']['value']
                // only for VueJS app

                $value = '';
                $field['value'] = (isset($values[$name])) ? $values[$name] : $value;
                $formatted[$name] = $field;
            }
        }

        // is settings_formatted section block
        $formatted['templates'] = $this->_getBlockTemplate($block);
        $formatted['templateSelected'] = $this->_getTemplateSelected($block);
        $formatted['default'] = $this->_getDefaultParams($block);

        return $formatted;
    }

    private function _getBlockTemplate(&$block)
    {
        $hookName = 'actionExtendBlockTemplate' . Tools::toCamelCase($this->code);
        $extraContent = Hook::exec(
            $hookName,
            [],
            null,
            true
        );
        // dump($hookName);

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
        // return $res;
        // dump($res);

        return $block['templates'] + $res;
    }

    private function _getDefaultParams($block)
    {
        $id_prettyblocks = (int) $block['id_prettyblocks'];
        $key = Tools::strtoupper($id_prettyblocks . '_default_params');
        // welcome = prettyblocks:views/templates/blocks/welcome.tpl
        $default_tpl = [
            'container' => true,
            'load_ajax' => false,
            'bg_color' => '',
        ];
        $defaultTemplate = Configuration::get($key);
        if (!$defaultTemplate) {
            return $default_tpl;
        }

        return json_decode($defaultTemplate, true);
    }

    /**
     * Format config for front office
     */
    private function _formatGetConfig($block, $context = 'front')
    {
        $formatted = [];
        $value = '';
        $config = ($block['config']['fields']) ?? [];
        if (is_array($config) && count($config) > 0) {
            foreach ($config as $field => $value) {
                $formatted[$field] = $this->_formatFieldConfigFront($field, $value, $block, $context);
            }
        }
        $formatted['templates'] = $this->_getBlockTemplate($block);
        $formatted['templateSelected'] = $this->_getTemplateSelected($block);
        $formatted['default'] = $this->_getDefaultParams($block);

        return $formatted;
    }

    /**
     * format field for smarty render.
     *
     * @return void
     */
    private function _formatFieldConfigFront($field, $value, $block, $context = 'front')
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

    private function _formatRepeaterDb($state, $repeaterDefault)
    {
        $res = [];

        foreach ($state as $s) {
            if (!$s) {
                return $res;
            }

            foreach ($s as $key => $value) {
                //  TODO Fix key with no default value
                if (isset($repeaterDefault[$key])) {
                    $repeaterDefault[$key]['value'] = ($value['value']) ?? '';
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
            $block[$field]['value'] = ($block[$field]['default']) ?? '';
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
        $formatted['id_prettyblocks'] = ($block['id_prettyblocks']) ?? '';
        $formatted['instance_id'] = ($block['instance_id']) ?? Tools::passwdGen(8, 'NUMERIC');
        $formatted['icon'] = ($block['icon']) ?? 'PhotographIcon';
        $formatted['module'] = $block['code']; // todo register module name
        $formatted['title'] = $block['name'];
        
        // if nameFrom params is present
        if(isset($block['nameFrom']) && isset($block['settings_formatted'][$block['nameFrom']]['value']))
        {
            $formatted['title'] = $block['settings_formatted'][$block['nameFrom']]['value'];
        }
        $formatted['is_parent'] = true;
        $formatted['is_child'] = false;
        $formatted['need_reload'] = ($block['need_reload']) ?? true;
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
                    'icon' => ($data['icon']) ?? 'SquaresPlusIcon',
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
        $context = Context::getContext();
        $id_lang = ($id_lang !== null) ? (int) $id_lang : $context->language->id;
        $id_shop = ($id_shop !== null) ? (int) $id_shop : $context->shop->id;

        $block = new PrettyBlocksModel(null, $id_lang, $id_shop);
        $block->zone_name = $zone_name;
        $block->code = $block_code;
        $block->name = $block_code;
        $array = [];
        $block->state = json_encode($array, true);
        $block->instance_id = uniqid();
        $block->save();
        $state = $block;

        $block = $block->mergeStateWithFields();
        $state_to_push = $block['state_to_push'];

        $state_db = json_decode($state->state, true);
        $state_db[1] = (object) $state_to_push;
        $encoded = json_encode($state_db);
        $state->state = $encoded;

        $state->save();

        return $block;
    }

    public function add($auto_date = true, $null_values = false)
    {
        $this->position = (int) DB::getInstance()->getValue('SELECT MAX(position)  FROM `' . _DB_PREFIX_ . 'prettyblocks`') + 1;
        parent::add();
    }

    public static function getThemeSettings($with_tabs = true, $context = 'front')
    {
        $theme_settings = Hook::exec('ActionRegisterThemeSettings', [], null, true);
        $res = [];
        $no_tabs = [];
        foreach ($theme_settings as $settings) {
            foreach ($settings as $name => $params) {
                $tab = ($params['tab']) ?? 'general';
                $params = self::_setThemeFieldValue($name, $params, $context);
                $res[$tab][$name] = $params;
                $no_tabs[$name] = ($params['value']) ?? false;
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
