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

include_once ("config.php");

spl_autoload_register(function($cn) {
    if(!str_starts_with($cn, "VeloFrame\\")) 
        return;
    $cna = explode("\\", $cn);
    $cn = $cna[sizeof($cna) - 1];
    if(file_exists(__DIR__."/".$cn.".php")) {
        require_once(__DIR__."/".$cn.".php");
    }
});

function print_debug($message, $severity) {
    if(!defined("DEBUG") || DEBUG < $severity)
        return;
    
    $pr_com = false;
    $prefix = "";
    if(defined("AUTO_COMMENT_DEBUG") && AUTO_COMMENT_DEBUG) {
        $pr_com = true;
    }
    switch($severity) {
        case "0":
            $prefix = "[ERROR] ";
            break;
        case "1":
            $prefix = "[WARN] ";
            break;
        default:
        case "2":
            $prefix = "[INFO] ";
            break;
    }
    echo (($pr_com)?"<!-- ":"").$prefix.$message.(($pr_com)?" -->":"");
}
