<?php
/**
 * Template.php
 * 
 * Simple Templating Engine to simplify management of HTML Templates with variables in PHP scripts.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

namespace WebFramework;

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
            $tstr = $template->output(FALSE);
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


        
    /**
     * Output / "Render" the template to HTML.
     *
     * @param  mixed $var_default   Which value to set unassigned variables to. When set to NULL, unassigned variables will be forwarded (e.g. when including other templates)
     * @return void
     */
    public function output($var_default = "") {

        if (ALLOW_INLINE_COMPONENTS)
            $this->html = $this->processInlineComponents($this->html);

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
            } else if ($var_default != FALSE) {
                $this->html = str_ireplace("{[$match]}", $var_default, $this->html);
                if(DEBUG > 1) echo "[INFO] Variable $match not set, defaulting to '$var_default'.";
            }
        }
        
        return $this->html;
    }

    function processInlineComponents($html) {
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
                    preg_match_all('/(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/', $attr_string, $attr_matches, PREG_SET_ORDER);
                    foreach ($attr_matches as $attr) {
                        $attributes[$attr[1]] = (!empty($attr[2])) ? $attr[2] : $attr[3];
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

}