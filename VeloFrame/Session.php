<?php
/**
 * Session.php
 * 
 * Provides (static) function to manage sessions and session variables
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.2
 */

namespace VeloFrame;

class Session {

    function __construct(bool $autostart = TRUE){
        if ($autostart) session_start();
    }
    
    /**
     * Sets a session variable to a specific value. Will return FALSE if failed.
     *
     * @param  string $key
     * @param  mixed $value
     * @return bool
     */
    static function set ($key, $value) {
        if(session_status() != PHP_SESSION_ACTIVE) {
            return false;
        }
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * Get a session variable. Will return $def_value if session variable does not exist.
     *
     * @param  string $key
     * @param  mixed $def_value
     * @return mixed
     */
    static function get ($key, $def_value = NULL) {
        if(!isset($_SESSION[$key])) {
            return $def_value;
        }
        return $_SESSION[$key];
    }

}
