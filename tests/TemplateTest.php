<?php
/**
 * TemplateTest.php
 * 
 * Comprehensive test suite for VeloFrame Template system
 * Tests variables, conditionals, loops, components, and template inclusion
 * 
 * @author Auto-generated for VeloFrame testing
 */

require_once __DIR__ . '/../VeloFrame/autoload.php';

// Initialize globals before class loading
$GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/..";

use VeloFrame\Template;
use VeloFrame\TemplateComponent;

class TemplateTest {
    
    private int $tests_run = 0;
    private int $tests_passed = 0;
    private array $failures = [];
    
    public function __construct() {
        // Set up globals for testing - Initialize early
        if (!isset($GLOBALS["WF_PROJ_DIR"])) {
            $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/..";
        }
        
        // Create test template directories if they don't exist
        if (!file_exists(__DIR__ . "/test_templates")) {
            mkdir(__DIR__ . "/test_templates", 0755, true);
        }
        if (!file_exists(__DIR__ . "/test_templates/templates")) {
            mkdir(__DIR__ . "/test_templates/templates", 0755, true);
        }
        if (!file_exists(__DIR__ . "/test_templates/templates/components")) {
            mkdir(__DIR__ . "/test_templates/templates/components", 0755, true);
        }
    }
    
    /**
     * Assert that two values are equal
     */
    private function assertEquals($expected, $actual, $message = "") {
        $this->tests_run++;
        if ($expected === $actual) {
            $this->tests_passed++;
            echo "✓ " . ($message ?: "Test passed") . "\n";
        } else {
            $this->failures[] = $message ?: "Assertion failed";
            echo "✗ " . ($message ?: "Test failed") . "\n";
            echo "  Expected: " . var_export($expected, true) . "\n";
            echo "  Actual: " . var_export($actual, true) . "\n";
        }
    }
    
    /**
     * Create a test template file
     */
    private function createTestTemplate($name, $content) {
        file_put_contents(__DIR__ . "/test_templates/templates/$name.htm", $content);
    }
    
    /**
     * Create a test component file
     */
    private function createTestComponent($name, $content) {
        file_put_contents(__DIR__ . "/test_templates/templates/components/$name.htm", $content);
    }
    
    /**
     * Test basic variable substitution
     */
    public function testBasicVariables() {
        echo "\n=== Testing Basic Variable Substitution ===\n";
        
        // Temporarily override WF_PROJ_DIR to use our test directory
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        try {
            $this->createTestTemplate("basic_vars", "Hello {[name]}! You are {[age]} years old.");
            
            $template = new Template("basic_vars");
            $template->setVariable("name", "John");
            $template->setVariable("age", "25");
            
            $output = $template->output();
            $this->assertEquals("Hello John! You are 25 years old.", $output, "Basic variable substitution");
            
            // Test unset variables with default
            $template2 = new Template("basic_vars");
            $template2->setVariable("name", "Jane");
            
            $output2 = $template2->output("DEFAULT");
            $this->assertEquals("Hello Jane! You are DEFAULT years old.", $output2, "Unset variable with default");
        } finally {
            // Restore original WF_PROJ_DIR
            $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
        }
    }
    
    /**
     * Test template inclusion
     */
    public function testTemplateInclusion() {
        echo "\n=== Testing Template Inclusion ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("header", "<h1>{[title]}</h1>");
        $this->createTestTemplate("main_with_header", "{![header]}<p>Content here</p>");
        
        $header = new Template("header");
        $header->setVariable("title", "Welcome");
        
        $main = new Template("main_with_header");
        $main->includeTemplate("header", $header);
        
        $output = $main->output();
        $this->assertEquals("<h1>Welcome</h1><p>Content here</p>", $output, "Template inclusion");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test components with variables
     */
    public function testComponents() {
        echo "\n=== Testing Components ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestComponent("card", '<div class="card"><h3>{[title]}</h3><p>{[content]}</p></div>');
        
        $component = new TemplateComponent("card", ["title" => "Test Card", "content" => "Test content"]);
        $output = $component->output();
        
        $expected = '<div class="card"><h3>Test Card</h3><p>Test content</p></div>';
        $this->assertEquals($expected, $output, "Component with named variables");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test component prefix/suffix functionality
     */
    public function testComponentPrefixSuffix() {
        echo "\n=== Testing Component Prefix/Suffix ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestComponent("prefix_test", 'Price: {$|[price]|.00}');
        
        $component = new TemplateComponent("prefix_test", ["price" => "19"]);
        $output = $component->output();
        
        $expected = 'Price: $19.00';
        $this->assertEquals($expected, $output, "Component prefix/suffix");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test conditionals (will initially fail, then pass after implementation)
     */
    public function testConditionals() {
        echo "\n=== Testing Conditionals (NEW FUNCTIONALITY) ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("conditional_test", 
            "Start {%if show_message%}Message is shown{%endif%} End");
        
        // Test with condition true
        $template = new Template("conditional_test");
        $template->setVariable("show_message", true);
        $output = $template->output();
        
        $expected = "Start Message is shown End";
        $this->assertEquals($expected, $output, "Conditional with true value");
        
        // Test with condition false
        $template2 = new Template("conditional_test");
        $template2->setVariable("show_message", false);
        $output2 = $template2->output();
        
        $expected2 = "Start  End";
        $this->assertEquals($expected2, $output2, "Conditional with false value");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test multiple conditionals in single template
     */
    public function testMultipleConditionals() {
        echo "\n=== Testing Multiple Conditionals ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("multiple_conditionals", 
            "{%if show_header%}Header{%endif%} {%if show_content%}Content{%endif%} {%if show_footer%}Footer{%endif%}");
        
        $template = new Template("multiple_conditionals");
        $template->setVariable("show_header", true);
        $template->setVariable("show_content", false);
        $template->setVariable("show_footer", true);
        
        $output = $template->output();
        $expected = "Header  Footer";
        $this->assertEquals($expected, $output, "Multiple conditionals in single template");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test if/else conditionals
     */
    public function testIfElseConditionals() {
        echo "\n=== Testing If/Else Conditionals ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("if_else_test", 
            "{%if user_logged_in%}Welcome back!{%else%}Please log in{%endif%}");
        
        // Test with condition true
        $template = new Template("if_else_test");
        $template->setVariable("user_logged_in", true);
        $output = $template->output();
        
        $this->assertEquals("Welcome back!", $output, "If/else with true condition");
        
        // Test with condition false
        $template2 = new Template("if_else_test");
        $template2->setVariable("user_logged_in", false);
        $output2 = $template2->output();
        
        $this->assertEquals("Please log in", $output2, "If/else with false condition");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test nested conditionals
     */
    public function testNestedConditionals() {
        echo "\n=== Testing Nested Conditionals ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("nested_test", 
            "{%if show_outer%}Outer {%if show_inner%}Inner{%else%}No Inner{%endif%} End{%endif%}");
        
        // Test both conditions true
        $template1 = new Template("nested_test");
        $template1->setVariable("show_outer", true);
        $template1->setVariable("show_inner", true);
        $output1 = $template1->output();
        $this->assertEquals("Outer Inner End", $output1, "Nested conditionals both true");
        
        // Test outer true, inner false
        $template2 = new Template("nested_test");
        $template2->setVariable("show_outer", true);
        $template2->setVariable("show_inner", false);
        $output2 = $template2->output();
        $this->assertEquals("Outer No Inner End", $output2, "Nested conditionals outer true, inner false");
        
        // Test outer false
        $template3 = new Template("nested_test");
        $template3->setVariable("show_outer", false);
        $template3->setVariable("show_inner", true);
        $output3 = $template3->output();
        $this->assertEquals("", $output3, "Nested conditionals outer false");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test loops
     */
    public function testLoops() {
        echo "\n=== Testing Loops ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("loop_test", 
            "Items: {%for item in items%}- {[item]} {%endfor%}");
        
        $template = new Template("loop_test");
        $template->setVariable("items", ["Apple", "Banana", "Cherry"]);
        
        $output = $template->output();
        $expected = "Items: - Apple - Banana - Cherry ";
        $this->assertEquals($expected, $output, "Basic loop functionality");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test loop variable assignments (abc.child1, abc.child2, etc.)
     */
    public function testLoopVariableAssignments() {
        echo "\n=== Testing Loop Variable Assignments ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("loop_vars_test", 
            "{%for person in people%}Name: {[person.name]}, Age: {[person.age]} {%endfor%}");
        
        $template = new Template("loop_vars_test");
        $template->setVariable("people", [
            ["name" => "John", "age" => 25],
            ["name" => "Jane", "age" => 30]
        ]);
        
        $output = $template->output();
        $expected = "Name: John, Age: 25 Name: Jane, Age: 30 ";
        $this->assertEquals($expected, $output, "Loop variable assignments");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test component conditionals
     */
    public function testComponentConditionals() {
        echo "\n=== Testing Component Conditionals ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestComponent("conditional_card", 
            '<div class="card">{%if show_title%}<h3>{[title]}</h3>{%endif%}<p>{[content]}</p></div>');
        
        $component = new TemplateComponent("conditional_card", [
            "title" => "Test Title",
            "content" => "Test content",
            "show_title" => true
        ]);
        $output = $component->output();
        
        $expected = '<div class="card"><h3>Test Title</h3><p>Test content</p></div>';
        $this->assertEquals($expected, $output, "Component with conditionals");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test component loops
     */
    public function testComponentLoops() {
        echo "\n=== Testing Component Loops ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestComponent("list_component", 
            '<ul>{%for item in items%}<li>{[item]}</li>{%endfor%}</ul>');
        
        $component = new TemplateComponent("list_component", [
            "items" => ["First", "Second", "Third"]
        ]);
        $output = $component->output();
        
        $expected = '<ul><li>First</li><li>Second</li><li>Third</li></ul>';
        $this->assertEquals($expected, $output, "Component with loops");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test complex nesting scenarios
     */
    public function testComplexNesting() {
        echo "\n=== Testing Complex Nesting ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("complex_nesting", 
            "{%if show_users%}Users: {%for user in users%}{%if user.active%}Active: {[user.name]} ({[user.email]}){%else%}Inactive: {[user.name]}{%endif%} {%endfor%}{%else%}No users to display{%endif%}");
        
        $template = new Template("complex_nesting");
        $template->setVariable("show_users", true);
        $template->setVariable("users", [
            ["name" => "John", "email" => "john@example.com", "active" => true],
            ["name" => "Jane", "email" => "jane@example.com", "active" => false],
            ["name" => "Bob", "email" => "bob@example.com", "active" => true]
        ]);
        
        $output = $template->output();
        $expected = "Users: Active: John (john@example.com) Inactive: Jane Active: Bob (bob@example.com) ";
        $this->assertEquals($expected, $output, "Complex nesting with loops and conditionals");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test loops with conditionals
     */
    public function testLoopsWithConditionals() {
        echo "\n=== Testing Loops with Conditionals ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("loop_conditional", 
            "{%for item in items%}{%if item.visible%}{[item.name]} {%endif%}{%endfor%}");
        
        $template = new Template("loop_conditional");
        $template->setVariable("items", [
            ["name" => "Item1", "visible" => true],
            ["name" => "Item2", "visible" => false],
            ["name" => "Item3", "visible" => true],
            ["name" => "Item4", "visible" => false]
        ]);
        
        $output = $template->output();
        $expected = "Item1 Item3 ";
        $this->assertEquals($expected, $output, "Loops with conditional filtering");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test empty arrays and missing variables
     */
    public function testEmptyArraysAndMissingVariables() {
        echo "\n=== Testing Empty Arrays and Missing Variables ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        // Test empty array
        $this->createTestTemplate("empty_array", 
            "Before loop {%for item in empty_array%}Item: {[item]}{%endfor%} After loop");
        
        $template1 = new Template("empty_array");
        $template1->setVariable("empty_array", []);
        $output1 = $template1->output();
        $this->assertEquals("Before loop  After loop", $output1, "Empty array loop");
        
        // Test missing array variable
        $template2 = new Template("empty_array");
        $output2 = $template2->output();
        $this->assertEquals("Before loop  After loop", $output2, "Missing array variable");
        
        // Test conditional with missing variable (should be false)
        $this->createTestTemplate("missing_var", 
            "{%if missing_variable%}This should not show{%else%}Default content{%endif%}");
        
        $template3 = new Template("missing_var");
        $output3 = $template3->output();
        $this->assertEquals("Default content", $output3, "Missing variable in conditional");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Test mixed variable types
     */
    public function testMixedVariableTypes() {
        echo "\n=== Testing Mixed Variable Types ===\n";
        
        $original_proj_dir = $GLOBALS["WF_PROJ_DIR"];
        $GLOBALS["WF_PROJ_DIR"] = __DIR__ . "/test_templates";
        
        $this->createTestTemplate("mixed_types", 
            "{%if is_string%}String: {[str_var]}{%endif%} {%if is_number%}Number: {[num_var]}{%endif%} {%if is_bool%}Bool is true{%endif%}");
        
        $template = new Template("mixed_types");
        $template->setVariable("is_string", true);
        $template->setVariable("str_var", "Hello World");
        $template->setVariable("is_number", true);
        $template->setVariable("num_var", 42);
        $template->setVariable("is_bool", true);
        
        $output = $template->output();
        $expected = "String: Hello World Number: 42 Bool is true";
        $this->assertEquals($expected, $output, "Mixed variable types");
        
        $GLOBALS["WF_PROJ_DIR"] = $original_proj_dir;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "Starting VeloFrame Template System Tests...\n";
        echo "==========================================\n";
        
        // Test existing functionality first
        $this->testBasicVariables();
        $this->testTemplateInclusion();
        $this->testComponents();
        $this->testComponentPrefixSuffix();
        
        // Test new functionality (will fail initially)
        $this->testConditionals();
        $this->testIfElseConditionals();
        $this->testNestedConditionals();
        $this->testMultipleConditionals();
        $this->testLoops();
        $this->testLoopVariableAssignments();
        $this->testComponentConditionals();
        $this->testComponentLoops();
        
        // Test edge cases and complex scenarios
        $this->testComplexNesting();
        $this->testLoopsWithConditionals();
        $this->testEmptyArraysAndMissingVariables();
        $this->testMixedVariableTypes();
        
        // Summary
        echo "\n==========================================\n";
        echo "Test Results:\n";
        echo "Tests Run: {$this->tests_run}\n";
        echo "Tests Passed: {$this->tests_passed}\n";
        echo "Tests Failed: " . ($this->tests_run - $this->tests_passed) . "\n";
        
        if (!empty($this->failures)) {
            echo "\nFailures:\n";
            foreach ($this->failures as $failure) {
                echo "- $failure\n";
            }
        }
        
        return $this->tests_passed === $this->tests_run;
    }
    
    /**
     * Clean up test files
     */
    public function cleanup() {
        $this->removeDirectory(__DIR__ . "/test_templates");
    }
    
    private function removeDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new TemplateTest();
    $success = $test->runAllTests();
    $test->cleanup();
    exit($success ? 0 : 1);
}