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
        
        // Process conditionals first
        $this->html = $this->processConditionals($this->html);
        
        // Process loops
        $this->html = $this->processLoops($this->html);
        
        // Process variables with prefix/suffix support (including dotted variables)
        $matches = array();
        $pattern = '/\{(?:([^{}]*)\|)?\[([a-zA-Z0-9_.]+)\](?:\|([^{}]*))?\}/s';
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

    /**
     * Internal Function to process conditionals
     *
     * @param  string $html
     * @return string
     */
    private function processConditionals(string $html) {
        // Process conditionals from innermost to outermost
        while (preg_match('/\{\%if\s+([a-zA-Z0-9_.]+)\%\}/', $html)) {
            // Find innermost conditional (no nested conditionals inside)
            $pattern = '/\{\%if\s+([a-zA-Z0-9_.]+)\%\}((?:(?!\{\%if\s+[a-zA-Z0-9_.]+\%\}).)*?)(?:\{\%else\%\}((?:(?!\{\%if\s+[a-zA-Z0-9_.]+\%\}).)*?))?\{\%endif\%\}/s';
            
            $html = preg_replace_callback($pattern, function ($matches) {
                $variable = $matches[1];
                $if_content = $matches[2];
                $else_content = isset($matches[3]) ? $matches[3] : '';
                
                // Check if variable is set and evaluates to true
                if (isset($this->vars[$variable]) && $this->vars[$variable]) {
                    return $if_content;
                } else {
                    return $else_content;
                }
            }, $html);
        }
        
        return $html;
    }

    /**
     * Internal Function to process loops
     *
     * @param  string $html
     * @return string
     */
    private function processLoops(string $html) {
        // Process for loops: {%for item in array%}content{%endfor%}
        $pattern = '/\{\%for\s+([a-zA-Z0-9_]+)\s+in\s+([a-zA-Z0-9_]+)\%\}(.*?)\{\%endfor\%\}/s';
        
        $html = preg_replace_callback($pattern, function ($matches) {
            $item_var = $matches[1];
            $array_var = $matches[2];
            $loop_content = $matches[3];
            
            // Check if array variable is set
            if (!isset($this->vars[$array_var]) || !is_array($this->vars[$array_var])) {
                return ''; // Return empty if array doesn't exist
            }
            
            $output = '';
            $array = $this->vars[$array_var];
            
            foreach ($array as $index => $item) {
                $loop_html = $loop_content;
                
                // Store original variables to restore later
                $original_vars = $this->vars;
                
                // Set loop item variables temporarily
                if (is_scalar($item)) {
                    $this->vars[$item_var] = (string)$item;
                }
                
                // Handle object/array item variables
                if (is_array($item)) {
                    foreach ($item as $key => $value) {
                        if (is_scalar($value)) {
                            $this->vars["$item_var.$key"] = (string)$value;
                        }
                    }
                }
                
                // Process conditionals in the loop content with current variables
                $loop_html = $this->processConditionals($loop_html);
                
                // Process variable substitution (only prefix/suffix style for components)
                $pattern = '/\{(?:([^{}]*)\|)?\[([a-zA-Z0-9_.]+)\](?:\|([^{}]*))?\}/s';
                $loop_html = preg_replace_callback($pattern, function ($matches) {
                    $varname = $matches[2];
                    $prefix = $matches[1];
                    $suffix = isset($matches[3]) ? $matches[3] : "";

                    if(isset($this->vars[$varname])) {
                        return $prefix.$this->vars[$varname].$suffix;
                    }
                    return $this->var_default;
                }, $loop_html);
                
                // Restore original variables
                $this->vars = $original_vars;
                
                $output .= $loop_html;
            }
            
            return $output;
        }, $html);
        
        return $html;
    }

}