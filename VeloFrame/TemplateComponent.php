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

namespace VeloFrame;

class TemplateComponent {

    private string $html;
    private string $name;
    /** @var array<string,mixed> $vars */
    private array $vars;
    private string $var_default;

    public function __construct(string $component_name,mixed ...$args) {
        $this->name = $component_name;
        $this->html = "";
        $this->open($component_name);
        if(is_array($args[0])) {
            $this->setNamedVarArray($args[0]);
        }else if($args != NULL) {
            $this->setVarArray($args);
        }
    }
    
    /**
     * Open a new Component by name. Will return false if the component could not be opened and true if the component has been loaded.
     *
     * @param  string $component_name
     * @return bool
     */
    public function open(string $component_name) {
        $files = new \RecursiveDirectoryIterator($GLOBALS["WF_PROJ_DIR"] . "/templates/components/");
        foreach (new \RecursiveIteratorIterator($files) as $file) {
            if(basename($file, ".htm") == $component_name) {
                $this->html = file_get_contents($file);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Public Alias of setVarArray with variadic parameter
     *
     * @param   array<string>   $args  Array of values to assign to the variables in order
     * @return  void
     */
    public function setVars(string ...$args) {
        $this->setVarArray($args);
    }

        
    /**
     * Set a variable value.
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setVariable(string $name, mixed $value) {
        $this->vars[$name] = $value;
    }

        
    /**
     * Merges the given key-value paired array with the internal var array.
     *
     * @param  array<string,mixed> $args
     * @return void
     */
    public function setNamedVarArray(array $args) {
        foreach($args as $key => $arg) {
            $this->setVariable($key,$arg);
        }
    }

    /**
     * Parses variable names based on occurance in the components and sets values based on argument array index positions.
     * 
     * @param   array<string,mixed> $args   Array of values to assign to the variables in order
     * @return  void
     */
    private function setVarArray(array $args) {
        $matches = array();
        preg_match_all('{\[(\w*)\]}', $this->html, $matches);

        if(defined("DEBUG") && DEBUG > 2 && sizeof($matches[1]) != sizeof($args)) {
            print_debug("Number of Arguments given and required by ".$this->name." are not equal.", 2);
        }

        $c = 0;
        foreach($args as $arg) {
            $this->vars[$matches[1][$c]] = $arg;
            $c++;
        }
    }
    
    /**
     * Render the Component to HTML
     *
     * @param  string $var_default
     * @return string
     */
    public function output(string $var_default = ""):string {
        $this->var_default = $var_default;
        $matches = array();
        $pattern = '/\{(?:([^{}]*)\|)?\[(\w+)\](?:\|([^{}]*))?\}/s';
        $this->html = preg_replace_callback($pattern, function ($matches) {
            $var_default = $this->var_default;
            $varname = $matches[2];
            $prefix = $matches[1];
            $suffix = isset($matches[3]) ? $matches[3] : "";

            if(!isset($this->vars[$varname])) {
                print_debug("Variable $varname not set, defaulting to '$var_default'.", 2);
                return $var_default;
            }
            return $prefix.$this->vars[$varname].$suffix;

        }, $this->html);

        return $this->html;
    }

}