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

        if (ALLOW_INLINE_COMPONENTS)
            $this->html = $this->processInlineComponents($this->html);

        #Scan for included templates
        $matches = array();
        preg_match_all('{!\[(\w*)\]}', $this->html, $matches);
        foreach($matches[1] as $match) {
            $this->html = str_ireplace("{![$match]}", "", $this->html);
            print_debug("Template include $match is required but not fulfilled!", 1);
        }

        #Scan for variables
        $matches = array();
        preg_match_all('{\[(\w*)\]}', $this->html, $matches);
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

}