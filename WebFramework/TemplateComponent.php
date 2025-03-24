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
        if(!file_exists(PROJ_DIR . "/templates/components/" . $component_name . ".htm")) {
            return false;
        }
        $this->html = file_get_contents(PROJ_DIR . "/templates/components/" . $component_name . ".htm");
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
        $matches = array();
        preg_match_all('{\[(\w*)\]}', $this->html, $matches);
        foreach($matches[1] as $match) {
            if(isset($this->vars[$match])) {
                $this->html = str_ireplace("{[$match]}", $this->vars[$match], $this->html);
            } else {
                $this->html = str_ireplace("{[$match]}", $var_default, $this->html);
                if(DEBUG > 1) echo "[INFO] Variable $match not set, defaulting to '$var_default'.";
            }
        }

        return $this->html;
    }

}