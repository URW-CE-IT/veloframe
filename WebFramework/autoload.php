<?php

spl_autoload_register(function($cn) {
    if(!str_starts_with($cn, "WebFramework\\")) 
        return;
    $cna = explode("\\", $cn);
    $cn = $cna[sizeof($cna) - 1];
    if(file_exists(__DIR__."/".$cn.".php")) {
        require_once(__DIR__."/".$cn.".php");
    }
        
});