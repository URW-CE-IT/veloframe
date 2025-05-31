<?php
/**
 * Input.php
 * 
 * Provides (static) functions to easily sanitize GET and POST user input
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.2
 */

namespace VeloFrame;

## Input Sources
define ("INPUT_SRC_GET", 0);
define ("INPUT_SRC_POST", 1);

## Input Types
# Text
define ("INPUT_TYPE_RAW", 0);           # Return the var without any sanitization (Dangerous!)
define ("INPUT_TYPE_STRING", 1);        # Try to strip HTML Tags and convert only special characters to HTML entities
define ("INPUT_TYPE_STRING_LOW", 2);    # Use only htmlspecialchars to convert special characters to HTML entities
define ("INPUT_TYPE_STRING_HIGH", 3);   # Try to strip HTML Tags and use htmlentities to convert all remaining characters to HTML entities if available
define ("INPUT_TYPE_URL", 4);           # Will remove any characters not commonly found in URLs (allowed: latin characters, digits, $-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=)
define ("INPUT_TYPE_EMAIL", 5);         # Will remove any characters not commonly found in E-Mail adresses (allowed: latin characters, digits, !#$%&'*+-=?^_`{|}~@.[])
# Number
define ("INPUT_TYPE_INT", 10);          # Will remove any characters except digits, + and - (WARNING! The dot is not included, so the numeric value might change after sanitization! If fractional parts may occur, use FLOAT sanitization and pass to intval())
define ("INPUT_TYPE_FLOAT", 11);        # Will remove any characters except digits, +, - and .(dot)

class Input {
    /**
     * Get GET / POST formencoded input vars, sanitizes them and returns them.
     *
     * @param  string $var_name  Name of the formencoded variable to retrieve
     * @param  int $var_type  Type of the variable to sanitize properly
     * @param  mixed $def_value Default value if the variable does not exist
     * @param  int $src       Whether to retrieve the variable from GET or POST
     * @return mixed
     */
    static function sanitize($var_name, $var_type = INPUT_TYPE_STRING, $def_value = NULL, $src = INPUT_SRC_GET) {

        $var = $def_value;
        
        if($src == INPUT_SRC_GET) {

            if(!isset($_GET[$var_name])) {
                return $var;
            }
            $var = $_GET[$var_name];

        } else {

            if(!isset($_POST[$var_name])) {
                return $var;
            }
            $var = $_POST[$var_name];

        }

        switch ($var_type) {
            case INPUT_TYPE_STRING:
                $var = strip_tags($var);
                $var = htmlspecialchars($var);
                break;
            case INPUT_TYPE_STRING_HIGH:
                $var = strip_tags($var);
                $var = htmlentities($var);
                break;
            case INPUT_TYPE_STRING_LOW:
                $var = htmlspecialchars($var);
                break;
            case INPUT_TYPE_URL:
                $var = filter_var($var, FILTER_SANITIZE_URL);
                break;
            case INPUT_TYPE_EMAIL:
                $var = filter_var($var, FILTER_SANITIZE_EMAIL);
                break;
            case INPUT_TYPE_INT:
                $var = filter_var($var, FILTER_SANITIZE_NUMBER_INT);
                break;
            case INPUT_TYPE_FLOAT:
                $var = filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;
        }

        return $var;

    }
}