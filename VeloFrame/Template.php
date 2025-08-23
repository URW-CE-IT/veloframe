<?php
/**
 * Template.php
 * 
 * Simple Templating Engine to simplify management of HTML Templates with variables in PHP scripts.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace VeloFrame;

class Template {

    private string $html;
    /** @var array<string,mixed> $vars */
    private array $vars;

    public function __construct(string $template_name = null) {
        $this->html = "";
        $this->vars = array();
        if($template_name !== null) {
            $ret = $this->open($template_name);
            if(!$ret) {
                throw new \Exception("Template could not be opened! Please check if the file exists and permissions are ok.");
            }
        }
    }
    
    /**
     * Open a new Template. Will replace the previously opened Template. Returns false if load failed and true if successful.
     *
     * @param  string $template_name
     * @return bool
     */
    public function open(string $template_name) {
        if(!file_exists($GLOBALS["WF_PROJ_DIR"] . "/templates/" . $template_name . ".htm")) {
            return false;
        }
        $this->html = file_get_contents($GLOBALS["WF_PROJ_DIR"] . "/templates/" . $template_name . ".htm");
        return true;
    }

    
    /**
     * Include a new sub-template into a subtemplate variable
     *
     * TODO: Migrate Template inclusion logic to output()
     * 
     * @param  string $name
     * @param  mixed $template (Accepts either a string (name of a template) or a Template object)
     * @return void
     */
    public function includeTemplate(string $name, mixed $template) {
        $tstr = $template;
        if(gettype($template) !== "string") {
            $tstr = $template->output(NULL);
        }

        $changes = 0;
        $this->html = str_ireplace("{![$name]}", $tstr, $this->html, $changes);
        if($changes == 0) print_debug("Sub-Template $name could not be included as the template was not found.\n", 1);
    }
    
    /**
     * setVariable
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setVariable(string $name, mixed $value) {
        $this->vars[$name] = $value;
    }
    
    /**
     * setHTML - Set HTML content directly (for testing purposes)
     *
     * @param  string $html
     * @return void
     */
    public function setHTML(string $html) {
        $this->html = $html;
    }
    
    /**
     * setComponent
     *
     * @param  string $name
     * @param  TemplateComponent $component
     * @return void
     */
    public function setComponent(string $name, TemplateComponent $component) {
        $this->vars[$name] = $component->output();
    }


        
    /**
     * Output / "Render" the template to HTML.
     *
     * @param  string|null $var_default   Which value to set unassigned variables to. When set to NULL, unassigned variables will be forwarded (e.g. when including other templates)
     * @return mixed
     */
    public function output(string|null $var_default = "") {
        if (is_null($var_default)) {
            return $this->html;
        }
        
        if (ALLOW_INLINE_COMPONENTS)
            $this->html = $this->processInlineComponents($this->html);

        // Process loops first (they handle their own conditionals internally)
        $this->html = $this->processLoops($this->html);
        
        // Process any remaining conditionals after loops
        $this->html = $this->processConditionals($this->html);

        #Scan for included templates
        $matches = array();
        preg_match_all('{!\[(\w*)\]}', $this->html, $matches);
        foreach($matches[1] as $match) {
            $this->html = str_ireplace("{![$match]}", "", $this->html);
            print_debug("Template include $match is required but not fulfilled!", 1);
        }

        #Scan for variables (including dotted variables like user.name)
        $matches = array();
        preg_match_all('{\[([a-zA-Z0-9_.]+)\]}', $this->html, $matches);
        foreach($matches[1] as $match) {
            if(isset($this->vars[$match])) {
                $this->html = str_ireplace("{[$match]}", $this->vars[$match], $this->html);
            } else if (!is_null($var_default)) {
                $this->html = str_ireplace("{[$match]}", $var_default, $this->html);
                print_debug("Variable $match not set, defaulting to '$var_default'.", 2);
            }
        }
        
        return $this->html;
    }
    
    /**
     * Internal Function to process inline components
     *
     * @param  string $html
     * @return string
     */
    private function processInlineComponents(string $html) {
        $pattern = '/<_([a-zA-Z0-9_]+)(\s+[^>]*)?>'
                 . '(?P<content>(?:[^<]+|<(?!\/?_[a-zA-Z0-9_]+)|(?R))*)'
                 . '<\/_\1>/s';
        while (preg_match($pattern, $html)) {
            $html = preg_replace_callback($pattern, function ($matches) {
                $tagName = $matches[1];
                $attr_string = isset($matches[2]) ? $matches[2] : "";
                $inner_html = isset($matches['content']) ? $matches['content'] : "";
                $inner_html = $this->processInlineComponents($inner_html);
                $attributes = array();
                if (!empty($attr_string)) {
                    preg_match_all('/(\w+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'))?/', $attr_string, $attr_matches, PREG_SET_ORDER);
                    foreach ($attr_matches as $attr) {
                        $attributes[$attr[1]] = isset($attr[2]) && $attr[2] !== '' ? $attr[2] : (isset($attr[3]) && $attr[3] !== '' ? $attr[3] : '');
                    }
                }
                if (strlen($inner_html) > 0) {
                    $attributes["content"] = $inner_html;
                }
                $component = new TemplateComponent($tagName, $attributes);
                $output = $component->output();

                return $output;
            }, $html);
        }
        return $html;
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
                
                // Process variable substitution
                foreach ($this->vars as $varname => $varvalue) {
                    if (is_scalar($varvalue)) {
                        $loop_html = str_replace("{[$varname]}", (string)$varvalue, $loop_html);
                    }
                }
                
                // Restore original variables
                $this->vars = $original_vars;
                
                $output .= $loop_html;
            }
            
            return $output;
        }, $html);
        
        return $html;
    }

}
