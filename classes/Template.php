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
        $this->vars = array();
        if($template_name !== null) {
            $this->readTemplate($template_name);
        }
    }

    public function readTemplate($template_name) {
        if(!file_exists(PROJ_DIR . "/templates/" . $template_name . ".htm")) {
            return false;
        }
        $this->html = file_get_contents(PROJ_DIR . "/templates/" . $template_name . ".htm");
    }

    /*
    Will include another template into the current one, ensuring that variables in the sub-template can be set directly in the full template.
    */
    public function includeTemplate($name, $template) {
        $tstr = $template;
        if(gettype($template) !== "string") {
            $tstr = $template->output();
        }

        $changes = 0;
        $this->html = str_ireplace("{![$name]}", $tstr, $this->html, $changes);
        if($changes == 0 && DEBUG)
            echo "[INFO] Sub-Template $name could not be included as the template was not found.\n";
    }

    public function setVariable($name, $value) {
        $this->vars[$name] = $value;
    }

    public function output() {
        foreach($this->vars as $name => $value) {
            $changes = 0;
            $this->html = str_ireplace("{[$name]}", $value, $this->html, $changes);
            if($changes == 0 && DEBUG)
                echo "[INFO] No changes made for variable $name.\n";
        }
        
        return $this->html;
    }

}