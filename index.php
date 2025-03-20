<?php

/**
 * index.php
 * 
 * WebFramework Starting File - Code Execution begins here.
 * All Web Requests are redirected to this file using the .htaccess in project root.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

define("PROJ_DIR", __DIR__);
define("DEBUG", 2);

spl_autoload_register(function($cn) {
    if(file_exists("classes/".$cn.".php"))
        require_once("classes/".$cn.".php");

    if(file_exists("functions/".$cn.".php"))
        require_once("functions/".$cn.".php");

    if(file_exists("pages/".$cn.".php"))
        require_once("pages/".$cn.".php");
});

$path = "index";
if(isset($_GET["rpath"])){
    $path = $_GET["rpath"];
}

$rh = new RoutingHandler();

$rh->register("index", new IndexHandler());

echo $rh->handle($path);