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

class Template
{

    private string $html;
    /** @var array<string,mixed> $vars */
    private array $vars;

    public function __construct(string $template_name = null)
    {
        $this->html = "";
        $this->vars = array();
        if ($template_name !== null) {
            $ret = $this->open($template_name);
            if (!$ret) {
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
    public function open(string $template_name)
    {
        if (!file_exists($GLOBALS["WF_PROJ_DIR"] . "/templates/" . $template_name . ".htm")) {
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
    public function includeTemplate(string $name, mixed $template)
    {
        $tstr = $template;
        if (gettype($template) !== "string") {
            $tstr = $template->output(NULL);
        }

        $changes = 0;
        $this->html = str_ireplace("{![$name]}", $tstr, $this->html, $changes);
        if ($changes == 0)
            print_debug("Sub-Template $name could not be included as the template was not found.\n", 1);
    }

    /**
     * setVariable
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function setVariable(string $name, mixed $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * setHTML - Set HTML content directly (for testing purposes)
     *
     * @param  string $html
     * @return void
     */
    public function setHTML(string $html)
    {
        $this->html = $html;
    }

    /**
     * setComponent
     *
     * @param  string $name
     * @param  TemplateComponent $component
     * @return void
     */
    public function setComponent(string $name, TemplateComponent $component)
    {
        $this->vars[$name] = $component->output();
    }



    /**
     * Output / "Render" the template to HTML.
     *
     * @param  string|null $var_default   Which value to set unassigned variables to. When set to NULL, unassigned variables will be forwarded (e.g. when including other templates)
     * @return mixed
     */
    public function output(string|null $var_default = "")
    {
        if (is_null($var_default)) {
            return $this->html;
        }

        if (ALLOW_INLINE_COMPONENTS)
            $this->html = $this->processInlineComponents($this->html);

        // Process loops first (they handle their own conditionals internally)
        $il = $this->processLoops($this->html, 0);
        if (!is_null($il)) {
            $this->html = $il;
        }

        // Process any remaining conditionals after loops
        $this->html = $this->processConditionals($this->html);

        #Scan for included templates
        $matches = array();
        preg_match_all('{!\[(\w*)\]}', $this->html, $matches);
        foreach ($matches[1] as $match) {
            $this->html = str_ireplace("{![$match]}", "", $this->html);
            print_debug("Template include $match is required but not fulfilled!", 1);
        }

        #Scan for variables (including dotted variables like user.name)
        $matches = array();
        preg_match_all('{\[([a-zA-Z0-9_.]+)\]}', $this->html, $matches);
        foreach ($matches[1] as $match) {
            $val = $this->getVar($match);
            if ($val !== null) {
                $this->html = str_ireplace("{[$match]}", $val, $this->html);
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
    private function processInlineComponents(string $html)
    {
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
    private function processConditionals(string $html)
    {
        // Process conditionals iteratively from innermost to outermost
        $max_iterations = 100;
        $iteration = 0;
        
        while ($iteration < $max_iterations && preg_match('/\{\%\s*if\s*([^\%]+)\s*\%\}/', $html)) {
            $iteration++;
            
            // Find all conditional blocks and their nesting levels
            $all_ifs = [];
            preg_match_all('/\{\%\s*if\s*([^\%]+)\s*\%\}/', $html, $if_matches, PREG_OFFSET_CAPTURE);
            
            foreach ($if_matches[0] as $idx => $if_match) {
                $if_pos = $if_match[1];
                $if_condition = trim($if_matches[1][$idx][0]);
                $if_tag_length = strlen($if_match[0]);
                
                // Find the matching endif by counting nested ifs
                $pos = $if_pos + $if_tag_length;
                $nesting_level = 1;
                $endif_pos = -1;
                
                while ($pos < strlen($html) && $nesting_level > 0) {
                    if (preg_match('/\{\%\s*(if|endif)(?:\s*[^\%]*)?\s*\%\}/', $html, $next_match, PREG_OFFSET_CAPTURE, $pos)) {
                        $match_pos = $next_match[0][1];
                        $match_type = $next_match[1][0];
                        
                        if ($match_type === 'if') {
                            $nesting_level++;
                        } elseif ($match_type === 'endif') {
                            $nesting_level--;
                            if ($nesting_level === 0) {
                                $endif_pos = $match_pos + strlen($next_match[0][0]);
                                break;
                            }
                        }
                        $pos = $match_pos + strlen($next_match[0][0]);
                    } else {
                        break;
                    }
                }
                
                if ($endif_pos !== -1) {
                    $content = substr($html, $if_pos + $if_tag_length, $endif_pos - $if_pos - $if_tag_length - strlen('{% endif %}'));
                    $nested_ifs = substr_count($content, '{% if ');
                    
                    $all_ifs[] = [
                        'start' => $if_pos,
                        'end' => $endif_pos,
                        'length' => $endif_pos - $if_pos,
                        'nested_count' => $nested_ifs,
                        'condition' => $if_condition
                    ];
                }
            }
            
            if (empty($all_ifs)) {
                break;
            }
            
            // Find the innermost conditional (one with least nested ifs, and shortest length if tied)
            usort($all_ifs, function($a, $b) {
                if ($a['nested_count'] !== $b['nested_count']) {
                    return $a['nested_count'] - $b['nested_count'];
                }
                return $a['length'] - $b['length'];
            });
            
            $innermost = $all_ifs[0];
            
            // Process this innermost conditional
            $if_pos = $innermost['start'];
            $if_condition = $innermost['condition'];
            
            // Re-parse this specific conditional block
            if (preg_match('/\{\%\s*if\s*([^\%]+)\s*\%\}/', $html, $if_match, PREG_OFFSET_CAPTURE, $if_pos)) {
                $if_tag_length = strlen($if_match[0][0]);
                
                // Find the matching endif and parse branches
                $pos = $if_pos + $if_tag_length;
                $nesting_level = 1;
                $endif_pos = -1;
                $branches = [];
                $current_content = '';
                $current_type = 'if';
                $current_condition = $if_condition;
                
                while ($pos < strlen($html) && $nesting_level > 0) {
                    if (preg_match('/\{\%\s*(if|elseif|else|endif)(?:\s*([^\%]*))?\s*\%\}/', $html, $next_match, PREG_OFFSET_CAPTURE, $pos)) {
                        $match_pos = $next_match[0][1];
                        $match_type = $next_match[1][0];
                        $match_condition = isset($next_match[2]) ? trim($next_match[2][0]) : '';
                        
                        // Add content before this tag to current branch
                        $current_content .= substr($html, $pos, $match_pos - $pos);
                        
                        if ($match_type === 'if') {
                            $nesting_level++;
                            $current_content .= $next_match[0][0];
                        } elseif ($nesting_level === 1 && ($match_type === 'elseif' || $match_type === 'else')) {
                            // Save current branch
                            $branches[] = [
                                'type' => $current_type,
                                'condition' => $current_condition,
                                'content' => $current_content
                            ];
                            
                            // Start new branch
                            $current_content = '';
                            $current_type = $match_type;
                            $current_condition = $match_condition;
                        } elseif ($match_type === 'endif') {
                            $nesting_level--;
                            if ($nesting_level === 0) {
                                // Save final branch
                                $branches[] = [
                                    'type' => $current_type,
                                    'condition' => $current_condition,
                                    'content' => $current_content
                                ];
                                $endif_pos = $match_pos + strlen($next_match[0][0]);
                                break;
                            } else {
                                $current_content .= $next_match[0][0];
                            }
                        }
                        
                        $pos = $match_pos + strlen($next_match[0][0]);
                    } else {
                        break;
                    }
                }
                
                if ($endif_pos === -1) {
                    break; // No matching endif found
                }
                
                // Evaluate conditions and select content
                $selected_content = '';
                foreach ($branches as $branch) {
                    if ($branch['type'] === 'if' || $branch['type'] === 'elseif') {
                        if ($this->evaluateCondition($branch['condition'])) {
                            $selected_content = $branch['content'];
                            break;
                        }
                    } elseif ($branch['type'] === 'else') {
                        $selected_content = $branch['content'];
                        break;
                    }
                }
                
                // Replace the entire conditional block with selected content
                $full_conditional = substr($html, $if_pos, $endif_pos - $if_pos);
                $html = str_replace($full_conditional, $selected_content, $html);
            } else {
                break;
            }
        }

        return $html;
    }

    /**
     * Evaluate a conditional expression
     *
     * @param  string $condition
     * @return bool
     */
    private function evaluateCondition(string $condition)
    {
        $condition = trim($condition);

        // Handle == comparison
        if (strpos($condition, '==') !== false) {
            list($left, $right) = explode('==', $condition, 2);
            $left = trim($left);
            $right = trim($right);
            $right = trim($right, "'\"");
            $left_val = $this->getVar($left);
            if ($left_val === null) return false;
            return (string)$left_val === $right;
        }

        // Handle != comparison
        if (strpos($condition, '!=') !== false) {
            list($left, $right) = explode('!=', $condition, 2);
            $left = trim($left);
            $right = trim($right);
            $right = trim($right, "'\"");
            $left_val = $this->getVar($left);
            if ($left_val === null) return true;
            return (string)$left_val !== $right;
        }

        // Handle >= comparison
        if (strpos($condition, '>=') !== false) {
            list($left, $right) = explode('>=', $condition, 2);
            $left = trim($left);
            $right = trim($right);
            $left_val = $this->getVar($left);
            if ($left_val === null) return false;
            $right_val = is_numeric($right) ? (float)$right : $right;
            $left_val = is_numeric($left_val) ? (float)$left_val : $left_val;
            return $left_val >= $right_val;
        }

        // Handle <= comparison
        if (strpos($condition, '<=') !== false) {
            list($left, $right) = explode('<=', $condition, 2);
            $left = trim($left);
            $right = trim($right);
            $left_val = $this->getVar($left);
            if ($left_val === null) return false;
            $right_val = is_numeric($right) ? (float)$right : $right;
            $left_val = is_numeric($left_val) ? (float)$left_val : $left_val;
            return $left_val <= $right_val;
        }

        // Handle > comparison
        if (strpos($condition, '>') !== false && strpos($condition, '>=') === false) {
            list($left, $right) = explode('>', $condition, 2);
            $left = trim($left);
            $right = trim($right);
            $left_val = $this->getVar($left);
            if ($left_val === null) return false;
            $right_val = is_numeric($right) ? (float)$right : $right;
            $left_val = is_numeric($left_val) ? (float)$left_val : $left_val;
            return $left_val > $right_val;
        }

        // Handle < comparison
        if (strpos($condition, '<') !== false && strpos($condition, '<=') === false) {
            list($left, $right) = explode('<', $condition, 2);
            $left = trim($left);
            $right = trim($right);
            $left_val = $this->getVar($left);
            if ($left_val === null) return false;
            $right_val = is_numeric($right) ? (float)$right : $right;
            $left_val = is_numeric($left_val) ? (float)$left_val : $left_val;
            return $left_val < $right_val;
        }

        // Simple variable check
        $value = $this->getVar($condition);
        if ($value !== null) {
            if (is_bool($value)) {
                return $value;
            }
            if (is_string($value)) {
                return $value !== '' && $value !== '0' && strtolower($value) !== 'false';
            }
            if (is_numeric($value)) {
                return (float)$value != 0;
            }
            return !empty($value);
        }

        return false;
    }

    /**
     * Internal Function to process loops
     *
     * @param  string $html
     * @return string
     */
    private function processLoops(string $html, int $depth = 0)
    {
        if ($depth > 5) {
            return $html;
        }
        
        // Find the first loop and process it completely (including nested loops)
        $max_iterations = 50;
        $iteration = 0;
        
        while ($iteration < $max_iterations) {
            $iteration++;
            
            // Find the start of the first loop
            if (!preg_match('/\{\%\s*for\s+([a-zA-Z0-9_]+)\s+in\s+([a-zA-Z0-9_.]+)\s*\%\}/', $html, $start_match, PREG_OFFSET_CAPTURE)) {
                break;
            }
            
            $start_pos = $start_match[0][1];
            $item_var = $start_match[1][0];
            $array_var = $start_match[2][0];
            $start_tag = $start_match[0][0];
            
            // Find the matching endfor by counting nested loops
            $pos = $start_pos + strlen($start_tag);
            $nesting_level = 1;
            $end_pos = -1;
            
            while ($pos < strlen($html) && $nesting_level > 0) {
                // Look for the next for or endfor
                if (preg_match('/\{\%\s*(for\s+[^%]+|endfor)\s*\%\}/', $html, $next_match, PREG_OFFSET_CAPTURE, $pos)) {
                    $match_pos = $next_match[0][1];
                    $match_content = $next_match[1][0];
                    
                    if (strpos($match_content, 'for') === 0) {
                        $nesting_level++;
                    } else { // endfor
                        $nesting_level--;
                        if ($nesting_level === 0) {
                            $end_pos = $match_pos + strlen($next_match[0][0]);
                            break;
                        }
                    }
                    $pos = $match_pos + strlen($next_match[0][0]);
                } else {
                    break;
                }
            }
            
            if ($end_pos === -1) {
                break;
            }
            
            // Extract the complete loop content
            $loop_start = $start_pos;
            $loop_length = $end_pos - $start_pos;
            $full_match = substr($html, $loop_start, $loop_length);
            $content_start = $start_pos + strlen($start_tag);
            $content_length = $end_pos - strlen('{% endfor %}') - $content_start;
            $loop_content = substr($html, $content_start, $content_length);
            
            $array = $this->getVar($array_var);
            if (!is_array($array)) {
                // Replace with empty string if array doesn't exist
                $html = str_replace($full_match, '', $html);
                continue;
            }

            $output = '';
            foreach ($array as $index => $item) {
                // Set loop variable without changing original vars
                $current_vars = $this->vars;
                
                if (is_scalar($item)) {
                    $current_vars[$item_var] = (string) $item;
                } elseif (is_array($item)) {
                    $current_vars[$item_var] = $item;
                    // Also set dotted variables
                    foreach ($item as $key => $value) {
                        if (is_scalar($value)) {
                            $current_vars[$item_var . ".$key"] = (string) $value;
                        } elseif (is_array($value)) {
                            $current_vars[$item_var . ".$key"] = $value;
                        }
                    }
                }

                // Temporarily update vars for this iteration
                $backup_vars = $this->vars;
                $this->vars = $current_vars;
                
                // Process the loop content (including any nested loops)
                $iteration_html = $loop_content;
                
                // Recursively process any nested loops with the current variable context
                $iteration_html = $this->processLoops($iteration_html, $depth + 1);
                
                // Process conditionals
                $iteration_html = $this->processConditionals($iteration_html);
                
                // Replace variables
                preg_match_all('/\{\[([^\]]+)\]\}/', $iteration_html, $var_matches);
                foreach (array_unique($var_matches[1]) as $var_name) {
                    $var_value = $this->getVar($var_name);
                    if ($var_value !== null && is_scalar($var_value)) {
                        $iteration_html = str_replace("{[$var_name]}", (string)$var_value, $iteration_html);
                    }
                }
                
                $output .= $iteration_html;
                
                // Restore original vars
                $this->vars = $backup_vars;
            }

            // Replace the processed loop with its output
            $html = str_replace($full_match, $output, $html);
        }

        return $html;
    }

    /**
     * Get a variable value, supporting dotted notation for nested access
     *
     * @param string $key
     * @return mixed
     */
    private function getVar(string $key)
    {
        if (isset($this->vars[$key])) {
            return $this->vars[$key];
        }
        $parts = explode('.', $key);
        if (count($parts) === 1) {
            return null;
        }
        $value = $this->vars;
        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }
        return $value;
    }

}
