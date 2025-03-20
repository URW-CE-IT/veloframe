<?php
/**
 * Template.php
 * 
 * Simple Templating Engine to simplify management of HTML Templates with variables in PHP scripts.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

class Template {

    private string $html;
    private array $vars;

    public function __construct($template_name = null) {
        $this->html = "";
        $this->vars = array();
        if($template_name !== null) {
            $this->open($template_name);
        }
    }

    public function open($template_name) {
        if(!file_exists(PROJ_DIR . "/templates/" . $template_name . ".htm")) {
            return false;
        }
        $this->html = file_get_contents(PROJ_DIR . "/templates/" . $template_name . ".htm");
    }

    /*
    TODO: Migrate Template Include Logic to output()
    */
    public function includeTemplate($name, $template) {
        $tstr = $template;
        if(gettype($template) !== "string") {
            $tstr = $template->output();
        }

        $changes = 0;
        $this->html = str_ireplace("{![$name]}", $tstr, $this->html, $changes);
        if($changes == 0 && DEBUG > 0)
            echo "[WARN] Sub-Template $name could not be included as the template was not found.\n";
    }

    public function setVariable($name, $value) {
        $this->vars[$name] = $value;
    }

    public function setComponent($name, TemplateComponent $component) {
        $this->vars[$name] = $component->output();
    }

    public function output($var_default = "") {

        #Scan for included templates
        $matches = array();
        preg_match_all('{!\[(\w*)\]}', $this->html, $matches);
        foreach($matches[1] as $match) {
            $this->html = str_ireplace("{![$match]}", "", $this->html);
            if(DEBUG > 0) {
                echo "[WARN] Template include $match is required but not fulfilled!";
            }
        }

        #Scan for variables
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