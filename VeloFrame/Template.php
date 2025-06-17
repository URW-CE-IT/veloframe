<?php
/**
 * Template.php
 * 
 * Simple Templating Engine to simplify management of HTML Templates with variables in PHP scripts.
 *
 * How it works:
 * - Templates are parsed into a tree of TemplateNode objects (not regex-based).
 * - Supports variables ({[var]}), subtemplates ({![subtpl]}), components (<_component>), conditionals ({% if %}), and loops ({% for %}).
 * - Variables can be set at any time; subtemplates are rendered lazily, so they always see the latest variables.
 * 
 * TODO:
 * - Add support for automatic APCu caching of static template elements (components with fixed attributes, subtemplates without variables).
 * - Optimize performance by improving memory access
 * 
 *
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1 (Parser-based Template Engine since 0.5)
 */

namespace VeloFrame;

/**
 * Represents a node in the template syntax tree
 */
class TemplateNode {
    /** @var string One of: 'root', 'text', 'var', 'subtemplate', 'component', 'if', 'ifnot', 'for', 'else_marker' */
    public string $type;
    
    /** @var string|array The raw value/content of this node */
    public $value;
    
    /** @var array<string,string> Arguments for variables, conditions, component attributes etc. */
    public $args;
    
    /** @var TemplateNode[] Child nodes for nested structures */
    public array $children;
    
    /** @var ?TemplateNode[] Nodes in the 'then' branch of if/ifnot blocks */
    public ?array $then = null;
    
    /** @var ?TemplateNode[] Nodes in the 'else' branch of if/ifnot blocks */
    public ?array $else = null;

    /**
     * @param string $type Node type
     * @param string|array $value Node value/content
     * @param array<string,string> $args Node arguments
     * @param TemplateNode[] $children Child nodes
     */
    public function __construct(string $type, $value = '', array $args = [], array $children = []) {
        $this->type = $type;
        $this->value = $value;
        $this->args = $args;
        $this->children = $children;
    }
}

class Template {
    /** @var string Raw template content */
    private string $html = '';
    
    /** @var array<string,mixed> Template variables */
    private array $vars = [];
    
    /** @var ?TemplateNode Root node of the parsed template */
    private ?TemplateNode $root = null;

    /**
     * Create a new template instance
     * 
     * @param ?string $template_name Optional template name to load
     * @throws \Exception If template could not be opened
     */
    public function __construct(?string $template_name = null) {
        if ($template_name !== null) {
            if (!$this->open($template_name)) {
                throw new \Exception("Template could not be opened! Please check if the file exists and permissions are ok.");
            }
        }
    }
    
    /**
     * Open and parse a template file
     * 
     * @param string $template_name Template name without extension
     * @return bool True if template was loaded successfully
     */
    public function open(string $template_name): bool {
        $path = $GLOBALS["WF_PROJ_DIR"] . "/templates/" . $template_name . ".htm";
        if (!file_exists($path)) {
            return false;
        }
        
        $this->html = file_get_contents($path);
        $this->root = $this->parseTemplate($this->html);
        
        return true;
    }

    /**
     * Include a new sub-template into a subtemplate variable
     * 
     * @param  string $name
     * @param  mixed $template (Accepts either a string (name of a template) or a Template object)
     * @return void
     */
    public function includeTemplate(string $name, mixed $template) {
        $tstr = $template;
        if (gettype($template) !== "string") {
            // Do NOT render the subtemplate immediately. Store the Template object for deferred rendering.
            $tstr = $template;
        }
        $this->vars[$name] = $tstr;
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
     * @param string|null $var_default Default value for unset variables
     * @return string The rendered HTML
     */
    public function output(?string $var_default = ""): string {
        if ($this->root === null) {
            $this->root = $this->parseTemplate($this->html);
        }
        return $this->renderNode($this->root, $var_default);
    }

    /**
     * Parse a template string into a syntax tree
     */
    private function parseTemplate(string $tpl): TemplateNode {
        $tokens = $this->tokenize($tpl);
        return $this->buildTree($tokens);
    }

    /**
     * Split template into tokens
     * 
     * @param string $tpl Template content
     * @return array List of tokens
     */
    private function tokenize(string $tpl): array {
        $tokens = [];
        $len = strlen($tpl);
        $i = 0;
        
        // Prepare regex patterns
        $patterns = [
            'component' => '/^<_([a-zA-Z0-9_]+)(\s+[^>]*)?\s*>/A',
            'block_start' => '/\{%\s*(if|ifnot|for)\b/',
            'block_end' => '/\{%\s*end(if|for)\s*%\}/A'
        ];
        
        while ($i < $len) {
            // Using strpos with offset is faster than substr for checking
            if ($i < $len - 1 && $tpl[$i] === '<' && $tpl[$i + 1] === '_') {
                if (preg_match($patterns['component'], substr($tpl, $i), $m)) {
                    $tag = $m[1];
                    $attr = $m[2] ?? '';
                    $open_len = strlen($m[0]);
                    
                    if (substr($attr, -1) === '/' || substr($tpl, $i + $open_len - 2, 2) === '/>') {
                        $tokens[] = [
                            'type' => 'component',
                            'name' => $tag,
                            'attr' => rtrim(substr($attr, 0, -1)),
                            'selfclose' => true,
                            'content' => ''
                        ];
                        $i += $open_len;
                        continue;
                    }
                    
                    // Find matching closing tag efficiently
                    $searchPos = $i + $open_len;
                    $tagDepth = 1;
                    $contentStart = $searchPos;
                    $closeTag = "</_$tag>";
                    $closeLen = strlen($closeTag);
                    
                    while ($searchPos < $len - 1) {
                        $nextOpen = strpos($tpl, '<_', $searchPos);
                        $nextClose = strpos($tpl, $closeTag, $searchPos);
                        
                        if ($nextClose === false) {
                            break;
                        }
                        
                        if ($nextOpen !== false && $nextOpen < $nextClose) {
                            if (preg_match('/^<_' . preg_quote($tag, '/') . '(\s|>)/', substr($tpl, $nextOpen))) {
                                $tagDepth++;
                            }
                            $searchPos = $nextOpen + 2;
                        } else {
                            $tagDepth--;
                            if ($tagDepth === 0) {
                                $tokens[] = [
                                    'type' => 'component',
                                    'name' => $tag,
                                    'attr' => $attr,
                                    'selfclose' => false,
                                    'content' => substr($tpl, $contentStart, $nextClose - $contentStart)
                                ];
                                $i = $nextClose + $closeLen;
                                break;
                            }
                            $searchPos = $nextClose + $closeLen;
                        }
                    }
                    continue;
                }
            }
            
            // Fast path for variable/subtemplate/control block detection
            if ($i < $len - 1 && $tpl[$i] === '{') {
                $char2 = $tpl[$i + 1] ?? '';
                if ($char2 === '[') {
                    // Variable
                    if ($end = strpos($tpl, ']}', $i)) {
                        $tokens[] = ['type' => 'var', 'value' => substr($tpl, $i + 2, $end - $i - 2)];
                        $i = $end + 2;
                        continue;
                    }
                }
                elseif ($i < $len - 2 && $char2 === '!' && $tpl[$i + 2] === '[') {
                    // Subtemplate
                    if ($end = strpos($tpl, ']}', $i)) {
                        $tokens[] = ['type' => 'subtemplate', 'value' => substr($tpl, $i + 3, $end - $i - 3)];
                        $i = $end + 2;
                        continue;
                    }
                }
                elseif ($char2 === '%') {
                    // Control blocks
                    $rest = substr($tpl, $i);
                    if (preg_match($patterns['block_start'], $rest, $m)) {
                        if ($end = strpos($rest, '%}')) {
                            $tokens[] = [
                                'type' => $m[1],
                                'value' => trim(substr($rest, strlen($m[0]), $end - strlen($m[0])))
                            ];
                            $i += $end + 2;
                            continue;
                        }
                    }
                    elseif (substr($rest, 0, 7) === "{% else") {
                        if ($end = strpos($rest, '%}')) {
                            $tokens[] = ['type' => 'else', 'value' => ''];
                            $i += $end + 2;
                            continue;
                        }
                    }
                    elseif (preg_match($patterns['block_end'], $rest, $m)) {
                        $tokens[] = ['type' => 'block_end', 'value' => ''];
                        $i += strlen($m[0]);
                        continue;
                    }
                }
            }
            
            // Text node (anything else) - find next delimiter efficiently
            $next = $len;
            $delimiters = ['<_', '{[', '{![', '{%'];
            foreach ($delimiters as $delim) {
                if ($pos = strpos($tpl, $delim, $i)) {
                    $next = min($next, $pos);
                }
            }
            
            if ($next > $i) {
                $tokens[] = ['type' => 'text', 'value' => substr($tpl, $i, $next - $i)];
            }
            $i = $next;
        }
        
        return $tokens;
    }

    private function buildTree(array &$tokens, int $depth = 0): TemplateNode {
        $root = new TemplateNode('root');
        
        if(!defined('MAX_DEPTH')) {
            define('MAX_DEPTH', 100);
            print_debug("MAX_DEPTH constant not defined, using default value of 100. Please define MAX_DEPTH in your VeloFrame config.", 2);
        }

        while (!empty($tokens)) {
            if ($depth > MAX_DEPTH) {
                $root->children[] = new TemplateNode('text', '<!-- Template parse error: maximum nesting exceeded -->');
                break;
            }
            
            $token = array_shift($tokens);
            switch ($token['type']) {
                case 'text':
                case 'var':
                case 'subtemplate':
                    $root->children[] = new TemplateNode($token['type'], $token['value']);
                    break;
                    
                case 'component':
                    $attr = [];
                    if (!empty($token['attr'])) {
                        preg_match_all('/(\w+)(?:\s*=\s*[\"\']?([^\"\']*)[\"\']?)?/', $token['attr'], $matches, PREG_SET_ORDER);
                        foreach ($matches as $m) {
                            $attr[$m[1]] = trim($m[2], '"\'');
                        }
                    }
                    
                    $children = [];
                    if (!$token['selfclose'] && !empty($token['content'])) {
                        $contentTokens = $this->tokenize($token['content']);
                        $contentTree = $this->buildTree($contentTokens, $depth + 1);
                        $children = $contentTree->children;
                    }
                    
                    $root->children[] = new TemplateNode('component', $token['name'], $attr, $children);
                    break;
                    
                case 'if':
                case 'ifnot':
                    $block = new TemplateNode($token['type'], trim($token['value']));
                    $block->then = [];
                    $block->else = [];
                    
                    $blockDepth = 1;
                    $collectingElse = false;
                    $currentTokens = [];
                    
                    while (!empty($tokens)) {
                        $current = array_shift($tokens);
                        
                        if ($current['type'] === $token['type']) {
                            $blockDepth++;
                            $currentTokens[] = $current;
                        }
                        elseif ($current['type'] === 'block_end') {
                            $blockDepth--;
                            if ($blockDepth === 0) {
                                // Process accumulated tokens
                                if (!empty($currentTokens)) {
                                    $branch = $this->buildTree($currentTokens, $depth + 1);
                                    if ($collectingElse) {
                                        $block->else = $branch->children;
                                    } else {
                                        $block->then = $branch->children;
                                    }
                                }
                                break;
                            } else {
                                $currentTokens[] = $current;
                            }
                        }
                        elseif ($current['type'] === 'else' && $blockDepth === 1) {
                            // Process tokens accumulated so far into then branch
                            if (!empty($currentTokens)) {
                                $branch = $this->buildTree($currentTokens, $depth + 1);
                                $block->then = $branch->children;
                            }
                            $currentTokens = []; // Reset for else branch
                            $collectingElse = true;
                        }
                        else {
                            $currentTokens[] = $current;
                        }
                    }
                    
                    // Process any remaining tokens if we haven't already
                    if (!empty($currentTokens)) {
                        $branch = $this->buildTree($currentTokens, $depth + 1);
                        if ($collectingElse) {
                            $block->else = $branch->children;
                        } else {
                            $block->then = $branch->children;
                        }
                    }
                    
                    $root->children[] = $block;
                    break;
                    
                case 'for':
                    $block = new TemplateNode('for', trim($token['value']));
                    $blockDepth = 1;
                    
                    while (!empty($tokens)) {
                        if ($tokens[0]['type'] === 'for') {
                            $blockDepth++;
                        }
                        elseif ($tokens[0]['type'] === 'block_end') {
                            $blockDepth--;
                            if ($blockDepth === 0) {
                                array_shift($tokens);
                                break;
                            }
                        }
                        
                        $current = array_shift($tokens);
                        if (in_array($current['type'], ['if', 'ifnot', 'for'])) {
                            array_unshift($tokens, $current);
                            $block->children[] = $this->buildTree($tokens, $depth + 1);
                        } else {
                            $block->children[] = new TemplateNode($current['type'], $current['value']);
                        }
                    }
                    
                    $root->children[] = $block;
                    break;
                    
                case 'block_end':
                    // We hit an end block without a matching start - ignore it
                    break;
            }
        }
        
        return $root;
    }

    /**
     * Render a node in the syntax tree
     * 
     * @param TemplateNode $node The node to render
     * @param string|null $var_default Default value for unset variables
     * @return string The rendered content
     */
    private function renderNode(TemplateNode $node, ?string $var_default): string {
        try {
            return match($node->type) {
                'root' => array_reduce($node->children, fn($output, $child) => 
                    $output . $this->renderNode($child, $var_default), ''),
                    
                'text' => (string)$node->value,
                
                'var' => $this->renderVariable($node, $var_default),
                
                'subtemplate' => $this->renderSubtemplateNode($node, $var_default),
                
                'component' => $this->renderComponent($node, $var_default),
                
                'if', 'ifnot' => $this->renderConditional($node, $var_default),
                
                'for' => $this->renderForNode($node, $var_default),
                
                default => ''
            };
        } catch (\Throwable $e) {
            return sprintf('<!-- Template render error: %s -->', htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Render a variable node
     */
    private function renderVariable(TemplateNode $node, ?string $var_default): string {
        $varName = trim((string)$node->value);
        
        // Handle empty variable name
        if ($varName === '') {
            return $var_default ?? '';
        }
        
        // If variable doesn't exist, return default
        if (!array_key_exists($varName, $this->vars)) {
            return $var_default ?? '';
        }
        
        $value = $this->vars[$varName];
        
        // Convert the value to string based on type
        return match(true) {
            is_bool($value) => $value ? '1' : '',
            is_array($value) => implode(', ', $value),
            is_null($value) => $var_default ?? '',
            default => (string)$value
        };
    }

    /**
     * Render a subtemplate node
     */
    private function renderSubtemplateNode(TemplateNode $node, ?string $var_default): string {
        $name = trim((string)$node->value);
        $subtpl = $this->vars[$name] ?? '';
        
        if ($subtpl instanceof Template) {
            // Pass variables to subtemplate
            foreach ($this->vars as $k => $v) {
                $subtpl->setVariable($k, $v);
            }
            return $subtpl->output($var_default);
        }
        
        return (string)$subtpl;
    }

    /**
     * Render a conditional node
     */
    private function renderConditional(TemplateNode $node, ?string $var_default): string {
        $condition = $this->evaluateCondition((string)$node->value);
        if ($node->type === 'ifnot') {
            $condition = !$condition;
        }
        
        // Decide which branch to render based on condition
        $branchToRender = $condition ? $node->then : $node->else;
        if (empty($branchToRender)) {
            return '';
        }
        
        return array_reduce($branchToRender, function($output, $child) use ($var_default) {
            return $output . $this->renderNode($child, $var_default);
        }, '');
    }

    /**
     * Evaluate a condition for if/ifnot blocks
     */
    private function evaluateCondition(string $condition): bool {
        $condition = trim($condition);
        if ($condition === '') {
            return false;
        }
        
        // Handle comparison operators
        if (str_contains($condition, '=')) {
            list($var, $value) = array_map('trim', explode('=', $condition, 2));
            if (!array_key_exists($var, $this->vars)) {
                return false;
            }
            $value = trim($value, '"\'');
            return (string)$this->vars[$var] === $value;
        }
        
        // Simple variable existence check
        if (!array_key_exists($condition, $this->vars)) {
            return false;
        }
        
        $value = $this->vars[$condition];
        
        // Type-specific truthiness checks
        return match(true) {
            is_bool($value) => $value,
            is_string($value) => $value !== '',
            is_numeric($value) => $value != 0,
            is_array($value) => !empty($value),
            is_null($value) => false,
            default => true
        };
    }

    /**
     * Render a component node
     * 
     * @param TemplateNode $node The component node to render
     * @param string|null $var_default Default value for unset variables
     * @return string The rendered component
     */
    private function renderComponent(TemplateNode $node, ?string $var_default): string {
        $attrs = is_array($node->args) ? $node->args : [];
        
        // Process attributes with variable interpolation
        if (!empty($attrs)) {
            $processedAttrs = [];
            foreach ($attrs as $k => $v) {
                if (str_contains($v, '{[')) {
                    $processed = preg_replace_callback('/\{\[([^\]]+)\]\}/', function($matches) use ($var_default) {
                        $varName = trim($matches[1]);
                        return array_key_exists($varName, $this->vars) 
                            ? (string)$this->vars[$varName] 
                            : ($var_default ?? '');
                    }, $v);
                    $processedAttrs[$k] = $processed;
                } else {
                    $processedAttrs[$k] = $v;
                }
            }
            $attrs = $processedAttrs;
        }
        
        // Add content from children if any
        if (!empty($node->children)) {
            $attrs['content'] = array_reduce($node->children, function($output, $child) use ($var_default) {
                return $output . $this->renderNode($child, $var_default);
            }, '');
        }
        
        // Create and render component
        $comp = new TemplateComponent((string)$node->value, $attrs);
        return $comp->output($var_default);
    }

    /**
     * Render a for node
     */
    private function renderForNode(TemplateNode $node, ?string $var_default): string {
        $loopVar = '';
        $loopItems = [];
        $output = '';
        
        // Extract loop variable and items from the node value
        if (preg_match('/^\s*([\w]+)\s+in\s+([\s\S]+?)\s*$/', trim((string)$node->value), $matches)) {
            $loopVar = $matches[1];
            $itemsExpr = $matches[2];
            
            // Evaluate the items expression
            $items = $this->evaluateExpression($itemsExpr);
            if (is_array($items)) {
                $loopItems = $items;
            }
        }
        
        // Render the body for each item in the loop
        foreach ($loopItems as $item) {
            // Set the loop variable
            $this->vars[$loopVar] = $item;
            
            // Render the child nodes
            $output .= array_reduce($node->children, function($output, $child) use ($var_default) {
                return $output . $this->renderNode($child, $var_default);
            }, '');
        }
        
        // Clear the loop variable
        unset($this->vars[$loopVar]);
        
        return $output;
    }

    /**
     * Evaluate an expression (for loops)
     */
    private function evaluateExpression(string $expr) {
        $expr = trim($expr);
        
        // Handle simple variable reference
        if (array_key_exists($expr, $this->vars)) {
            return $this->vars[$expr];
        }
        
        // Handle quoted strings
        if (preg_match('/^["\'](.+)["\']$/', $expr, $matches)) {
            return $matches[1];
        }
        
        // Handle JSON array syntax
        if (preg_match('/^\[(.*)\]$/', $expr, $matches)) {
            $json = '[' . $matches[1] . ']';
            $array = json_decode($json, true);
            return is_array($array) ? $array : [];
        }
        
        // Default to empty array on parse error
        return [];
    }
}
