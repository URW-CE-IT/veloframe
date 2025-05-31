<?php
/**
 * index.php
 * 
 * VeloFrame Starting File - Code Execution begins here.
 * All Web Requests are redirected to this file using the .htaccess in project root.
 * 
 * @author Patrick Matthias Garske <patrick@garske.link>
 * @since 0.1
 */

include_once("VeloFrame/autoload.php");

use VeloFrame as WF;

$server = new WF\Server();
$server->setRoutingHandler(new WF\RoutingHandler());
$server->serve();