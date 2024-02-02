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
use ScssPhp\ScssPhp\Compiler;

class PrettyBlocksCompiler
{
    public $entries = [];
    public $out = '';
    public $import_path = [];
    private $_sass = '';
    private $_compiled = '';
    private $outTarget = 'css';
    public $compiler;
    private $filesToExtract = [];
    private $theme_name = '';
    private $id_shop;

    public function __construct($entries = [], $out = '')
    {
        $this->entries = $entries;
        $this->out = $out;
        $this->compiler = new Compiler();
    }

    public function setThemeName($theme_name)
    {
        $this->theme_name = $theme_name;

        return $this;
    }

    public function setIdShop($id_shop)
    {
        $this->id_shop = $id_shop;

        return $this;
    }

    /**
     * Set entries
     *
     * @return PrettyBlockCompiler
     */
    public function setEntries($entries)
    {
        if (!is_array($entries)) {
            $entries = [$entries];
        }
        foreach ($entries as $entry) {
            if (!in_array($entry, $this->entries)) {
                $this->entries[] = $entry;
            }
        }

        return $this;
    }

    /**
     * Set files to extract
     *
     * @param $entries
     *
     * @return PrettyBlockCompiler
     */
    public function setFilesToExtract($entries)
    {
        if (!is_array($entries)) {
            $entries = [$entries];
        }
        foreach ($entries as $entry) {
            if (!in_array($entry, $this->filesToExtract)) {
                $this->filesToExtract[] = $entry;
            }
        }

        return $this;
    }

    /**
     * Set Output path
     *
     * @return PrettyBlockCompiler
     */
    public function setOuput($out)
    {
        $path = HelperBuilder::pathFormattedFromString($out);
        $path = rtrim($path, '/');
        if (is_file($path)) {
            $this->out .= $path;
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension == 'scss') {
                $this->outTarget = 'scss';
            }
        }

        return $this;
    }

    /**
     * Reset entries
     *
     * @return PrettyBlockCompiler
     */
    public function resetEntries()
    {
        $this->entries = [];

        return $this;
    }

    /**
     * Reset output path
     *
     * @return PrettyBlockCompiler
     */
    public function resetOut()
    {
        $this->out = '';

        return $this;
    }

    /**
     * Set imports path of Compiler
     *
     * @return PrettyBlockCompiler
     */
    public function setImportPaths($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        foreach ($paths as $entry) {
            if (!in_array($entry, $this->import_path)) {
                $path = HelperBuilder::pathFormattedFromString($entry);
                $this->import_path[] = $path;
            }
        }

        return $this;
    }

    /**
     * Compile sass file
     *
     * @return PrettyBlockCompiler
     */
    public function compile()
    {
        if (count($this->entries) <= 0) {
            return false;
        }

        $scss = $this->compiler;
        // set import path
        if (count($this->import_path) > 0) {
            $scss->setImportPaths($this->import_path);
        }
        // add entries
        foreach ($this->entries as $entry) {
            $path = HelperBuilder::pathFormattedFromString($entry);
            $path = rtrim($path, '/');
            if (is_file($path)) {
                $this->_sass .= Tools::file_get_contents($path);
            }
        }
        $this->filterVars();

        // add files to extract
        $this->_sass .= $this->filterFile($this->filesToExtract);

        $compile = $scss->compileString($this->_sass)->getCss();
        if ($compile !== '' && $this->outTarget == 'css') {
            // compile sass to css
            $this->_compiled = $compile;
        } else {
            // compile sass to sass
            $this->_compiled = $this->_sass;
        }

        return $this;
    }

    /**
     * Get Settings Vars of Builder and replace it
     *
     * @return PrettyBlockCompiler
     */
    protected function filterVars()
    {
        if ($this->_sass != '') {
            $to_filter = $this->_sass;
            $to_filter = $this->filterContent($to_filter);
            $this->_sass = $to_filter;
        }

        return $this;
    }

    /**
     * Filter file and replace vars
     *
     * @param $files
     *              $files = [
     *              '$/modules/your_module/views/css/vars.scss'
     *              ];
     *
     * @return string
     */
    public function filterFile($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        $to_filter = '';
        foreach ($files as $file) {
            $path = HelperBuilder::pathFormattedFromString($file);
            $path = rtrim($path, '/');
            if (is_file($path)) {
                $to_filter .= Tools::file_get_contents($path);
            }
        }

        return $this->filterContent($to_filter);
    }

    /**
     * Filter content and replace vars
     *
     * @param $scss
     *
     * @return string
     */
    public function filterContent($scss)
    {
        $re = '/.*(\$SETTINGS_.\S*);*\b/';
        preg_match_all($re, $scss, $vars);
        $content = str_replace('$SETTINGS_', '', $vars[1]);
        foreach ($content as $var) {
            $scss = str_replace('$SETTINGS_' . $var, $this->getSettingsValue($var, $var), $scss);
        }

        return $scss;
    }

    private function getSettingsValue($settings, $defaultValue)
    {
        $value = $defaultValue;
        $settings = $this->getThemeSettings();
        if (isset($settings[$defaultValue])) {
            $value = $settings[$defaultValue];
        }

        return $value;
    }

    private function getThemeSettings()
    {
        $psContext = Context::getContext();

        return PrettyBlocksModel::getThemeSettings(false, 'front', $this->id_shop);
    }

    /**
     * Write sass file if is compiled
     *
     * @return PrettyBlockCompiler
     */
    public function write()
    {
        if ($this->out != '' && $this->_compiled != '') {
            file_put_contents($this->out, $this->_compiled);
        }

        return $this;
    }

    /**
     * Compile Sass and Write
     *
     * @return PrettyBlockCompiler
     */
    public function compileAndWrite()
    {
        $this->compile()->write();

        return $this;
    }
}
