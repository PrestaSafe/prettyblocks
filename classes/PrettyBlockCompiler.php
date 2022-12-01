<?php 

use ScssPhp\ScssPhp\Compiler;
class PrettyBlocksCompiler
{
    public $entries = [];
    public $out = '';
    public $import_path = [];
    private $_sass = '';
    private $_compiled = '';

    public function __construct($entries = [], $out = '')
    {
        $this->entries = $entries;
        $this->out = $out;
    }
    /**
     * Set entries
     * @return PrettyBlockCompiler
     */
    public function setEntries($entries)
    {
        if(!is_array($entries))
        {
            $entries = [$entries];
        }
        foreach($entries as $entry)
        {
            if(!in_array($entry,$this->entries))
            {
                $this->entries[] = $entry;
            }
        }
        return $this;
    }
    /**
     * Set Output path
     * @return PrettyBlockCompiler
     */
    public function setOuput($out)
    {
        $path = HelperBuilder::pathFormatterFromString($out);
        $path = rtrim($path,'/');
        if (is_file($path))
        {
            $this->out .= $path;
        }
        return $this;
    }

    /**
     * Reset entries
     * @return PrettyBlockCompiler
     */
    public function resetEntries()
    {
        $this->entries = [];
        return $this;
    }
    /**
     * Reset output path
     * @return PrettyBlockCompiler
     */
    public function resetOut()
    {
        $this->out = '';
        return $this;
    }
     /**
     * Set imports path of Compiler
     * @return PrettyBlockCompiler
     */
    public function setImportPaths($paths)
    {
        if(!is_array($paths))
        {
            $paths = [$paths];
        }
        foreach($paths as $entry)
        {
            if(!in_array($entry,$this->import_path))
            {
                $path = HelperBuilder::pathFormatterFromString($entry);
                $this->import_path[] = $path;
            }
        }
        return $this;
    }

     /**
     * Compile sass file
     * @return PrettyBlockCompiler
     */
    public function compile()
    {
        if(count($this->entries) <= 0)
        {
            return false; 
        }            

        $scss = new Compiler();
        if(count($this->import_path) > 0)
        {
            $scss->setImportPaths($this->import_path);
        }
        foreach($this->entries as $entry)
        {
            $path = HelperBuilder::pathFormatterFromString($entry);
            $path = rtrim($path,'/');
            if (is_file($path))
            {
                $this->_sass .= file_get_contents($path);
            }
        }
        $this->filterVars();
        $compile = $scss->compileString($this->_sass)->getCss();
        // really strange issue... 
        if($compile !== "")
        {
            $this->_compiled = $compile;
        }else{
            $this->_compiled = $this->_sass;
        }
        
        return $this;
    } 
     /**
     * Get Settings Vars of Builder and replace it
     * @return PrettyBlockCompiler
     */
    protected function filterVars()
    {
        if($this->_sass != '')
        {
            $to_filter = $this->_sass;
            $re = '/.*(\$SETTINGS_.\S*);*\b/';
            preg_match_all($re, $this->_sass, $vars);
            $content = str_replace('$SETTINGS_','',$vars[1]);
            foreach($content as $var)
            {
                $to_filter = str_replace('$SETTINGS_'.$var,TplSettings::getSettings($var, $var), $to_filter);
            }
            $this->_sass = $to_filter;
        }
        return $this;
    }

    /**
     * Write sass file if is compiled
     * @return PrettyBlockCompiler
     */
    public function write()
    {   
        if($this->out != '' && $this->_compiled != '')
        {
            file_put_contents($this->out,$this->_compiled);
        }

        return $this;
    }
    /**
     * Compile Sass and Write
     * @return PrettyBlockCompiler
     */
    public function compileAndWrite()
    {
        $this->compile()->write();
        return $this;
    }
}
