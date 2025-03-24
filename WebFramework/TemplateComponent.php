<?php
/**
 * TemplateComponent.php
 * 
 * Extends the Templating Engine by adding Component support.
 * Components are small Blocks of HTML with a fixed structure which can be repeatedly used in a single document.
 * A simple div-block can be a Component, as well as a nested structure of different div-blocks.
 * 
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace WebFramework;

class TemplateComponent {

    private string $html;
    private string $name;
    private array $vars;

    public function __construct($component_name, ...$args) {
        $this->name = $component_name;
        $this->html = "";
        $this->open($component_name);
        if(is_array($args[0])) {
            $this->setNamedVarArray($args[0]);
        }else if($args != NULL) {
            $this->setVarArray($args);
        }
    }

    public function open($component_name) {
        $files = new \RecursiveDirectoryIterator(PROJ_DIR . "/templates/components/");
        foreach (new \RecursiveIteratorIterator($files) as $file) {
            if(basename($file, ".htm") == $component_name) {
                $this->html = file_get_contents($file);
                return;
            }
        }
        return false;
    }
    
    /**
     * Public Alias of setVarArray with variadic parameter
     *
     * @param   mixed   $args  Array of values to assign to the variables in order
     * @return  void
     */
    public function setVars(...$args) {
        setVarArray($args);
    }

        
    /**
     * Set a variable value.
     *
     * @param  mixed $name
     * @param  mixed $value
     * @return void
     */
    public function setVariable($name, $value) {
        $this->vars[$name] = $value;
    }

    public function setNamedVarArray($args) {
        foreach($args as $key => $arg) {
            $this->setVariable($key,$arg);
        }
    }

    /**
     * Parses variable names based on occurance in the components and sets values based on argument array index positions.
     * 
     * @param   Array $args   Array of values to assign to the variables in order
     * @return  void
     */
    private function setVarArray($args) {
        $matches = array();
        preg_match_all('{\[(\w*)\]}', $this->html, $matches);

        if(DEBUG && sizeof($matches[1]) != sizeof($args)) {
            echo "[INFO] Number of Arguments given and required by ".$this->name." are not equal.";
        }

        $c = 0;
        foreach($args as $arg) {
            $this->vars[$matches[1][$c]] = $arg;
            $c++;
        }
    }

    public function output($var_default = ""):string {
        $this->var_default = $var_default;
        $matches = array();
        $pattern = '/\{(?:([^{}]*)\|)?\[(\w+)\](?:\|([^{}]*))?\}/s';
        $this->html = preg_replace_callback($pattern, function ($matches) {
            $var_default = $this->var_default;
            $varname = $matches[2];
            $prefix = $matches[1];
            $suffix = isset($matches[3]) ? $matches[3] : "";

            if(!isset($this->vars[$varname])) {
                if(DEBUG > 1) echo "[INFO] Variable $varname not set, defaulting to '$var_default'.";
                return $var_default;
            }
            return $prefix.$this->vars[$varname].$suffix;

        }, $this->html);

        return $this->html;
    }

}