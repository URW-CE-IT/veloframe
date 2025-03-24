<?php
define("PROJ_DIR", __DIR__);
spl_autoload_register(function($cn) {
    if(file_exists("classes/".$cn.".php"))
        require_once("classes/".$cn.".php");

    if(file_exists("pages/".$cn.".php"))
        require_once("pages/".$cn.".php");
});